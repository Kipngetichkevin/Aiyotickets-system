<?php

return [

    // Commission kept by the platform on every paid ticket and booking, in percent.
    'platform_fee_percent' => (float) env('PLATFORM_FEE_PERCENT', 5),

    // Revenue becomes withdrawable only after the event/experience has ended
    // (protects against cancellations and refunds).
    'payout_after_end' => (bool) env('PAYOUT_AFTER_END', true),

    // Smallest payout a merchant can request, in KES.
    'payout_minimum' => (int) env('PAYOUT_MINIMUM', 100),

];
