@extends('legal._layout')

@php($appName = \App\Helpers\SystemHelper::appName())
@php($contact = \App\Helpers\SystemHelper::contactEmail() ?: 'support@aiyotickets.com')

@section('title', 'Refund & cancellation policy')
@section('updated', '15 October 2026')

@section('body')
<p>This policy explains when you can get your money back for tickets and experiences bought on {{ $appName }}. Every event and experience page shows its own refund rule above the checkout, and the same rule appears on your tickets.</p>

<h2>1. Each listing has one of these rules</h2>
<ul>
    <li><strong>No refunds</strong>: tickets can't be cancelled by the buyer.</li>
    <li><strong>Refunds up to 7 days before</strong>: cancel at least 7 days before the start for a full refund.</li>
    <li><strong>Refunds up to 48 hours before</strong>: cancel at least 48 hours before the start for a full refund.</li>
</ul>
<p>Where refunds are allowed, open your ticket link (in your ticket email or SMS) and tap <strong>Request a refund</strong>. Tickets already scanned at the gate can't be refunded. Refunded tickets stop working immediately.</p>

<h2>2. If the organiser cancels</h2>
<p>Whatever the rule, you get a <strong>full refund</strong> of what you paid, including every lipa mdogo mdogo payment, automatically. We'll tell you by email (and SMS where available). You don't need to do anything.</p>

<h2>3. If the event is postponed</h2>
<p>Your tickets stay valid for the new date. If you can't make it, you may ask for a full refund from your ticket page within <strong>{{ \App\Support\RefundPolicy::POSTPONED_DAYS }} days</strong> of being told about the new date, whatever the listing's usual rule.</p>

<h2>4. Lipa mdogo mdogo (instalments)</h2>
<ul>
    <li>Your deposit holds your place. If the balance isn't paid by the due date, the booking is cancelled, the <strong>deposit is kept</strong> and any payments after the deposit are refunded.</li>
    <li>If the organiser cancels, everything you paid is refunded.</li>
    <li>To cancel an instalment booking yourself, contact us. The listing's rule applies to what you've paid after the deposit.</li>
</ul>

<h2>5. How and when refunds are paid</h2>
<p>Refunds go back to the M-Pesa number you paid with, usually within <strong>7 working days</strong>. If that number can no longer receive money, reply to your ticket email with another number in your name. The full ticket price is returned; M-Pesa charges you paid to Safaricom are not.</p>

<h2>6. Other cases</h2>
<p>If you were charged but didn't receive tickets, or paid twice, contact us with your M-Pesa receipt number and we'll fix it or refund you. If an event goes ahead but is materially different from what was advertised, contact us within 7 days and we'll look into it with the organiser.</p>

<h2>7. Contact</h2>
<p>Email <a href="mailto:{{ $contact }}">{{ $contact }}</a> with your order number (it starts with ORD-).</p>
@endsection
