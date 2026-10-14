<x-mail::message>
# Payment received

Hi {{ $order->initiator }}, we received **KES {{ number_format($amount) }}** towards order **{{ $order->order_no }}**.

<x-mail::panel>
**Total:** KES {{ number_format($order->total_amount) }}
**Paid so far:** KES {{ number_format($order->amount_paid) }}
**Balance:** KES {{ number_format($order->balance_due) }}
**Pay by:** {{ $order->due_date?->format('l, d M Y') }}
</x-mail::panel>

Your place is held. Your QR tickets will be sent as soon as the balance is fully paid. Pay any amount, any time, with the button below — no login needed.

<x-mail::button :url="\App\Http\Controllers\InstalmentController::payLinkFor($order)">
Pay balance
</x-mail::button>

If the balance isn't paid by the due date, the booking is cancelled: the deposit is kept and any later payments are refunded.
@include('mail._account')

{{ $appName }}
</x-mail::message>
