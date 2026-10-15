<?php

return [

    // Commission kept by the platform on every paid ticket and booking, in percent.
    'platform_fee_percent' => (float) env('PLATFORM_FEE_PERCENT', 5),

    // Revenue becomes withdrawable only after the event/experience has ended
    // (protects against cancellations and refunds).
    'payout_after_end' => (bool) env('PAYOUT_AFTER_END', true),

    // Only sell tickets inside their sales window (ticket start_date/end_date).
    // Run `php artisan tickets:sale-dates` to find tickets to fix before turning this on.
    'enforce_sale_dates' => (bool) env('ENFORCE_SALE_DATES', false),

    // Who gets emailed about errors and failed backups in production (comma-separated).
    'alert_email' => env('ALERT_EMAIL', env('ADMIN_EMAIL')),

    // Smallest payout a merchant can request, in KES.
    'payout_minimum' => (int) env('PAYOUT_MINIMUM', 100),

];
