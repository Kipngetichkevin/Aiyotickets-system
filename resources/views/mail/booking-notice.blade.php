<x-mail::message>
@if ($kind === 'cancelled')
# {{ $details['name'] }} has been cancelled

Hi {{ $order->initiator }}, we're sorry — the organiser has cancelled **{{ $details['name'] }}**.

@if (!empty($details['reason']))
<x-mail::panel>
{{ $details['reason'] }}
</x-mail::panel>
@endif

**KES {{ number_format($details['amount'] ?? 0) }}** from order {{ $order->order_no }} will be refunded to the M-Pesa number you paid with. You don't need to do anything.
@elseif ($kind === 'postponed')
# {{ $details['name'] }} has a new date

Hi {{ $order->initiator }}, **{{ $details['name'] }}** has moved to **{{ $details['when'] }}**.

@if (!empty($details['note']))
<x-mail::panel>
{{ $details['note'] }}
</x-mail::panel>
@endif

Your tickets are still valid for the new date — nothing to do. If you can't make it, you can ask for a full refund from your ticket page until **{{ $details['deadline'] }}**.

<x-mail::button :url="\App\Http\Controllers\TicketViewController::linkFor($order)">
View my tickets
</x-mail::button>
@else
# Refund requested

Hi {{ $order->initiator }}, we've received your refund request for order **{{ $order->order_no }}**.

**KES {{ number_format($details['amount'] ?? 0) }}** will be sent to the M-Pesa number you paid with, usually within a few working days. The refunded tickets no longer work at the gate.
@endif

@include('mail._account')

{{ $appName }}
</x-mail::message>
