<?php

namespace App\Console\Commands;

use App\Models\MpesaStk;
use App\Models\User;
use App\Services\DarajaService;
use App\Services\MpesaPaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Go-live checklist: everything that must be true on the server for payments,
 * tickets and emails to work. Exits non-zero while anything fails.
 */
class ProductionCheck extends Command
{
    public const HEARTBEAT = 'aiyo:scheduler-heartbeat';

    protected $signature = 'aiyo:check
        {--live : Also ask Safaricom for an access token to prove the M-Pesa keys work}
        {--mail= : Also send a test email to this address}';

    protected $description = 'Check the server is ready to take real payments';

    private int $failures = 0;

    private int $warnings = 0;

    public function handle(): int
    {
        $this->section('App');
        $url = (string) config('app.url');
        $this->check(config('app.env') === 'production', 'APP_ENV is production', 'APP_ENV is "' . config('app.env') . '"');
        $this->check(!config('app.debug'), 'APP_DEBUG is off', 'APP_DEBUG is on: errors would show code and settings to visitors');
        $this->check((bool) config('app.key'), 'APP_KEY is set', 'APP_KEY is empty: run php artisan key:generate');
        $this->check(str_starts_with($url, 'https://'), "APP_URL is HTTPS ({$url})", "APP_URL is not HTTPS ({$url}): links in emails and the M-Pesa callback need it");
        $this->check((bool) config('session.secure'), 'Session cookies are HTTPS-only', 'SESSION_SECURE_COOKIE is false', warnOnly: true);
        $this->check(is_dir(public_path('storage')), 'public/storage is linked (posters and photos show)', 'public/storage is missing: run php artisan storage:link');

        $this->section('Database');
        try {
            DB::connection()->getPdo();
            $this->ok('Database connection works');
            $ran = DB::table('migrations')->count();
            $this->check($ran > 0, 'Migrations have run', 'No migrations recorded: run php artisan migrate --force');
            $admin = User::role('admin')->first();
            $this->check((bool) $admin, 'An admin account exists' . ($admin ? " ({$admin->email})" : ''), 'No admin account: set ADMIN_EMAIL / ADMIN_PASSWORD and run php artisan db:seed');
        } catch (\Throwable $e) {
            $this->problem('Database connection failed: ' . $e->getMessage());
        }

        $this->section('M-Pesa');
        $mpesa = config('mpesa');
        $this->check($mpesa['environment'] === 'production', 'MPESA_ENVIRONMENT is production', 'MPESA_ENVIRONMENT is "' . $mpesa['environment'] . '": payments are test money');
        foreach (['consumer_key' => 'MPESA_CONSUMER_KEY', 'consumer_secret' => 'MPESA_CONSUMER_SECRET', 'passkey' => 'MPESA_PASSKEY', 'shortcode' => 'MPESA_BUSINESS_SHORTCODE'] as $key => $env) {
            $this->check(!empty($mpesa[$key]), "{$env} is set", "{$env} is empty");
        }
        $this->check(in_array($mpesa['transaction_type'], ['paybill', 'till'], true),
            'Transaction type: ' . $mpesa['transaction_type'], 'MPESA_TRANSACTION_TYPE must be paybill or till');
        if ($mpesa['transaction_type'] === 'till') {
            $this->check(!empty($mpesa['party_b']), 'Till number (MPESA_PARTY_B) is set', 'MPESA_PARTY_B (the Till number) is empty');
        }
        $callback = (string) $mpesa['callback_url'];
        $public   = str_starts_with($callback, 'https://') && !preg_match('#//(localhost|127\.|10\.|192\.168\.)#', $callback);
        $this->check($public, "Callback URL is public HTTPS ({$callback})", "Callback URL must be public HTTPS, Safaricom can't reach {$callback}");

        if ($this->option('live')) {
            try {
                app(DarajaService::class)->getAccessToken();
                $this->ok('Safaricom accepted the consumer key and secret');
            } catch (\Throwable $e) {
                $this->problem('Safaricom rejected the credentials: ' . $e->getMessage());
            }
        } else {
            $this->line('  <fg=gray>Run with --live to test the keys against Safaricom.</>');
        }

        $this->section('Email');
        $mailer = config('mail.default');
        $this->check(!in_array($mailer, ['log', 'array'], true), "Mail is sent with \"{$mailer}\"", "MAIL_MAILER is \"{$mailer}\": no email leaves the server (verification links, tickets)");
        if ($mailer === 'smtp') {
            $host = (string) config('mail.mailers.smtp.host');
            $this->check(!in_array($host, ['mailpit', 'mailhog', 'localhost', '127.0.0.1', ''], true),
                "SMTP server: {$host}", "MAIL_HOST is \"{$host}\": that's a developer mail catcher, real inboxes never get these emails");
        }
        $from = (string) config('mail.from.address');
        $this->check($from !== '' && !str_ends_with($from, '@example.com'), "Emails come from {$from}", "MAIL_FROM_ADDRESS is \"{$from}\"");

        if ($to = $this->option('mail')) {
            try {
                \Illuminate\Support\Facades\Mail::raw(
                    'This is a test from ' . config('app.name') . '. If you can read it, ticket emails will arrive too.',
                    fn ($m) => $m->to($to)->subject('Test email from ' . config('app.name'))
                );
                $this->ok("Test email sent to {$to}: check the inbox (and spam)");
            } catch (\Throwable $e) {
                $this->problem("Test email to {$to} failed: " . $e->getMessage());
            }
        } else {
            $this->line('  <fg=gray>Run with --mail=you@example.com to send a test email.</>');
        }

        $this->section('Background jobs');
        $this->check(config('queue.default') !== 'sync', 'Queue: ' . config('queue.default'),
            'QUEUE_CONNECTION is sync: SMS and emails are sent inside the M-Pesa callback and slow it down', warnOnly: true);

        $beat = Cache::get(self::HEARTBEAT);
        $this->check($beat && now()->timestamp - $beat < 180,
            'Scheduler is running (last run ' . ($beat ? (now()->timestamp - $beat) . 's ago' : 'never') . ')',
            'Scheduler has not run in the last 3 minutes: add the cron entry (see .env.example). Without it payments are not reconciled, instalment reminders and queued emails never go out');

        if (Schema::hasTable('jobs')) {
            $stuck = DB::table('jobs')->where('created_at', '<', now()->subMinutes(10)->timestamp)->count();
            $this->check($stuck === 0, 'No queued jobs waiting over 10 minutes', "{$stuck} queued job(s) waiting over 10 minutes: the queue worker isn't running");
        }
        if (Schema::hasTable('failed_jobs')) {
            $failed = DB::table('failed_jobs')->count();
            $this->check($failed === 0, 'No failed jobs', "{$failed} failed job(s): see php artisan queue:failed", warnOnly: true);
        }

        $this->section('Backups & alerts');
        $alertTo = config('aiyo.alert_email');
        $this->check((bool) $alertTo, "Errors and failed backups are emailed to {$alertTo}", 'ALERT_EMAIL is empty: nobody hears about errors or failed backups');
        $disks = (array) config('backup.backup.destination.disks');
        $this->check(count(array_diff($disks, ['local'])) > 0, 'Backups are copied off this server (' . implode(', ', $disks) . ')',
            'Backups only go to this server (BACKUP_DISKS=local): if the server is lost, so are they', warnOnly: true);
        try {
            $newest = collect(\Spatie\Backup\BackupDestination\BackupDestinationFactory::createFromArray(app(\Spatie\Backup\Config\Config::class)))
                ->map(fn ($d) => $d->newestBackup()?->date())->filter()->max();
            $this->check($newest && $newest->gt(now()->subDays(2)),
                'Newest backup: ' . ($newest ? $newest->diffForHumans() : 'none'),
                'No backup in the last 2 days' . ($newest ? ' (newest ' . $newest->diffForHumans() . ')' : '') . ': check the cron entry and that mysqldump is installed');
        } catch (\Throwable $e) {
            $this->problem('Could not read backups: ' . $e->getMessage());
        }

        $this->section('Payments');
        $unfulfilled = MpesaStk::where('status', MpesaPaymentService::COMPLETED)->whereNull('order_id')->whereNull('instalment_order_id')->count();
        $this->check($unfulfilled === 0, 'Every paid M-Pesa payment has its order', "{$unfulfilled} paid payment(s) without an order: open Admin → M-Pesa payments → Paid, no order");

        $this->newLine();
        if ($this->failures) {
            $this->error(" {$this->failures} problem(s) to fix before taking real payments" . ($this->warnings ? ", {$this->warnings} warning(s)" : '') . '. ');
            return self::FAILURE;
        }

        $this->info($this->warnings ? " Ready, with {$this->warnings} warning(s). " : ' Ready for real payments. ');
        return self::SUCCESS;
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->line("<options=bold>{$title}</>");
    }

    private function check(bool $ok, string $pass, string $problem, bool $warnOnly = false): void
    {
        if ($ok) {
            $this->ok($pass);
        } elseif ($warnOnly) {
            $this->warnings++;
            $this->line("  <fg=yellow>!</> {$problem}");
        } else {
            $this->problem($problem);
        }
    }

    private function ok(string $message): void
    {
        $this->line("  <fg=green>✓</> {$message}");
    }

    private function problem(string $message): void
    {
        $this->failures++;
        $this->line("  <fg=red>✗</> {$message}");
    }
}
