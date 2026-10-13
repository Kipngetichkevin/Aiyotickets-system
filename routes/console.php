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
