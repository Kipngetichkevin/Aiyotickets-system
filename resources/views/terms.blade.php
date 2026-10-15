@extends('legal._layout')

@php($appName = \App\Helpers\SystemHelper::appName())
@php($contact = \App\Helpers\SystemHelper::contactEmail() ?: 'support@aiyotickets.com')

@section('title', 'Terms and conditions')
@section('updated', '15 October 2026')

@section('body')
<p>These terms apply to everyone who uses {{ $appName }} to buy or sell tickets for events and experiences. By using the site you agree to them.</p>

<h2>1. What we do</h2>
<p>{{ $appName }} is a marketplace: organisers list events and experiences, and we sell the tickets and collect payment on their behalf. The organiser is responsible for the event itself: its content, safety, timing and any age or entry rules.</p>

<h2>2. Buying tickets</h2>
<ul>
    <li>Tickets are subject to availability and are confirmed only once M-Pesa confirms your payment.</li>
    <li>Each ticket has its own QR code and admits one person once. Keep your ticket link private: anyone with the code can use it.</li>
    <li>With lipa mdogo mdogo, your place is held by the deposit and tickets are issued once the balance is fully paid by the due date.</li>
    <li>Organisers and venues may refuse entry for safety reasons, for breaking venue rules, or if a ticket has already been used.</li>
</ul>

<h2>3. Refunds</h2>
<p>Refunds follow our <a href="{{ route('refund-policy') }}">refund &amp; cancellation policy</a> and the rule shown on each listing. If an organiser cancels, you are refunded in full.</p>

<h2>4. Organisers</h2>
<ul>
    <li>Organisers must be approved before selling, give accurate details, and run their events as advertised.</li>
    <li>We keep a platform fee of {{ rtrim(rtrim(number_format((float) config('aiyo.platform_fee_percent'), 2), '0'), '.') }}% of each sale. Sales become payable to the organiser after the event ends, less refunds owed to buyers.</li>
    <li>Organisers who cancel must allow buyers to be refunded. We may suspend organisers who mislead buyers.</li>
</ul>

<h2>5. Acceptable use</h2>
<p>Don't copy or forge tickets, resell them above face value through the platform, interfere with the site, or use it for anything unlawful. We may cancel tickets obtained this way.</p>

<h2>6. Liability</h2>
<p>We're responsible for selling and delivering your tickets and handling refunds as described. The event itself is the organiser's responsibility. Nothing in these terms limits your rights under the Consumer Protection Act, 2012.</p>

<h2>7. Privacy</h2>
<p>How we handle your data is explained in our <a href="{{ route('privacy') }}">privacy policy</a>.</p>

<h2>8. Changes and governing law</h2>
<p>We may update these terms and will post changes here. These terms are governed by the laws of Kenya, and the courts of Kenya have jurisdiction.</p>

<h2>9. Contact</h2>
<p>Email <a href="mailto:{{ $contact }}">{{ $contact }}</a>.</p>
@endsection
