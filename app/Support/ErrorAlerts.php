<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Emails ALERT_EMAIL when something breaks in production (a failing payment callback
 * mustn't go unnoticed). Each distinct error is sent at most once per 30 minutes, and
 * the alert itself can never break the request. Laravel already skips expected
 * exceptions (404s, validation, auth) before this is called.
 */
class ErrorAlerts
{
    public static function report(Throwable $e): void
    {
        $to = config('aiyo.alert_email');
        if (!$to || !app()->isProduction()) {
            return;
        }

        try {
            $key = 'error-alert:' . md5(get_class($e) . $e->getFile() . $e->getLine());
            if (!Cache::add($key, true, now()->addMinutes(30))) {
                return;
            }

            $request = app()->runningInConsole() ? 'console: ' . implode(' ', $_SERVER['argv'] ?? []) : request()->method() . ' ' . request()->fullUrl();
            $body = implode("\n", [
                get_class($e) . ': ' . $e->getMessage(),
                'at ' . $e->getFile() . ':' . $e->getLine(),
                'during ' . $request,
                'time ' . now()->toDateTimeString(),
                '',
                'Same error is muted for 30 minutes. Full trace in storage/logs/laravel.log.',
                '',
                collect(explode("\n", $e->getTraceAsString()))->take(15)->implode("\n"),
            ]);

            Mail::raw($body, fn ($m) => $m->to(array_map('trim', explode(',', $to)))
                ->subject('[' . config('app.name') . '] Error: ' . mb_strimwidth($e->getMessage() ?: get_class($e), 0, 90, '…')));
        } catch (Throwable) {
            // Never let an alert failure hide the original error
        }
    }
}
