<x-mail::message>
@if ($kind === 'lapsed')
# Your booking was cancelled

Hi {{ $order->initiator }}, the balance for order **{{ $order->order_no }}** wasn't paid by {{ $order->due_date?->format('d M Y') }}, so the booking has been cancelled and the place released.

The deposit of KES {{ number_format($order->deposit_amount) }} is non-refundable.
@if ($order->refund > 0)
The KES {{ number_format($order->refund) }} you paid after the deposit will be refunded to you.
@endif
@else
# Reminder: balance due in {{ $daysLeft }} {{ $daysLeft === 1 ? 'day' : 'days' }}

Hi {{ $order->initiator }}, your place for order **{{ $order->order_no }}** is held.

<x-mail::panel>
**Paid:** KES {{ number_format($order->amount_paid) }} of {{ number_format($order->total_amount) }}
**Balance:** KES {{ number_format($order->balance_due) }}
**Pay by:** {{ $order->due_date?->format('l, d M Y') }}
</x-mail::panel>

<x-mail::button :url="\App\Http\Controllers\InstalmentController::payLinkFor($order)">
Pay balance
</x-mail::button>

If it isn't paid in time, the booking is cancelled: the deposit is kept and later payments are refunded.
@include('mail._account')
@endif

{{ $appName }}
</x-mail::message>
