<x-mail::message>
# Payment received

Hi {{ $order->initiator }}, we received **KES {{ number_format($amount) }}** towards order **{{ $order->order_no }}**.

<x-mail::panel>
**Total:** KES {{ number_format($order->total_amount) }}
**Paid so far:** KES {{ number_format($order->amount_paid) }}
**Balance:** KES {{ number_format($order->balance_due) }}
**Pay by:** {{ $order->due_date?->format('l, d M Y') }}
</x-mail::panel>

Your place is held. Your QR tickets will be sent as soon as the balance is fully paid. You can pay any amount, any time, from your account.

<x-mail::button :url="route('instalments.mine')">
Pay balance
</x-mail::button>

If the balance isn't paid by the due date, the booking is cancelled: the deposit is kept and any later payments are refunded.

{{ $appName }}
</x-mail::message>
