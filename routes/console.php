<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Requires the server cron entry: * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('mpesa:reconcile')->everyTwoMinutes()->withoutOverlapping();

// Sends queued mail (tickets emails) without a separate long-running worker process
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')->everyMinute()->withoutOverlapping();

// Lipa mdogo mdogo reminders and cancellation of plans past their due date
Schedule::command('instalments:process')->hourly()->withoutOverlapping();

// Proof the cron entry is running, read by `php artisan aiyo:check`
Schedule::call(fn () => Illuminate\Support\Facades\Cache::forever(App\Console\Commands\ProductionCheck::HEARTBEAT, now()->timestamp))
    ->everyMinute()->name('scheduler-heartbeat');

// Backups (spatie/laravel-backup; needs mysqldump on the server): database nightly,
// uploads weekly, old copies pruned, and an email if the newest backup is over a day old
Schedule::command('backup:clean')->dailyAt('01:00')->withoutOverlapping();
Schedule::command('backup:run --only-db')->dailyAt('01:30')->withoutOverlapping();
Schedule::command('backup:run --only-files')->weeklyOn(0, '02:00')->withoutOverlapping();
Schedule::command('backup:monitor')->dailyAt('07:00');
