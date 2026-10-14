<x-mail::message>
# Your tickets are ready

Hi {{ $order->initiator }}, thank you for your purchase. Show the QR code for each ticket at the entrance.

**Order:** {{ $order->order_no }}
@if ($order->transaction_code)
**M-Pesa receipt:** {{ $order->transaction_code }}
@endif
**Total paid:** KES {{ number_format($order->total_amount) }}

@foreach ($items as $item)
<x-mail::panel>
**{{ $item['title'] }}**@if ($item['subtitle']) — {{ $item['subtitle'] }}@endif

@if ($item['when']){{ $item['when'] }}@endif @if ($item['where'])· {{ $item['where'] }}@endif

Ticket holder: {{ $item['name'] }}

<img src="{{ $message->embedData(base64_decode($item['qr_base64']), $item['code'] . '.png', 'image/png') }}" alt="{{ $item['code'] }}" width="180" height="180">

Code: **{{ $item['code'] }}**
</x-mail::panel>
@endforeach

<x-mail::button :url="\App\Http\Controllers\TicketViewController::linkFor($order)">
View my tickets
</x-mail::button>

If a QR code won't scan, the gate staff can type the code instead.
@include('mail._account')

{{ $appName }}
</x-mail::message>
