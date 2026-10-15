@extends('legal._layout')

@php($appName = \App\Helpers\SystemHelper::appName())
@php($contact = \App\Helpers\SystemHelper::contactEmail() ?: 'support@aiyotickets.com')

@section('title', 'Privacy policy')
@section('updated', '15 October 2026')

@section('body')
<p>{{ $appName }} sells tickets for events and experiences in Kenya. This policy explains what personal data we collect, why, who we share it with, and your rights under Kenya's Data Protection Act, 2019.</p>

<h2>1. What we collect</h2>
<ul>
    <li><strong>When you buy:</strong> your name, email address, phone number and, if asked, gender; what you bought; and the M-Pesa receipt number, amount and paying phone number Safaricom sends us. We never see or store your M-Pesa PIN.</li>
    <li><strong>When you create an account:</strong> the above plus your password, stored scrambled and never readable.</li>
    <li><strong>Organisers:</strong> business name, ID or company registration number, KRA PIN, contacts and M-Pesa or bank details for payouts.</li>
    <li><strong>At the gate:</strong> when and through which gate your ticket was scanned.</li>
    <li><strong>Technical:</strong> your IP address and browser in security logs, and a session cookie that keeps you logged in and holds your cart. We don't use advertising cookies.</li>
</ul>

<h2>2. Why we use it</h2>
<ul>
    <li>To sell and deliver your tickets, confirm your payment and let you into the event (our contract with you).</li>
    <li>To send tickets, receipts, instalment reminders and changes such as cancellations (our contract with you).</li>
    <li>To pay organisers, issue refunds and keep accounting and tax records (legal obligation).</li>
    <li>To prevent fraud, ticket misuse and abuse of the platform (legitimate interest).</li>
</ul>
<p>We don't sell your data, and we don't send marketing without your consent.</p>

<h2>3. Who we share it with</h2>
<ul>
    <li><strong>The organiser</strong> of the event or experience you book: your name, contact details and ticket, so they can run the event and contact you about it.</li>
    <li><strong>Safaricom (M-Pesa)</strong>, to process payments and refunds.</li>
    <li><strong>Our email and SMS providers</strong>, to deliver your tickets and messages.</li>
    <li><strong>Our hosting provider</strong>, which stores the data on our behalf.</li>
    <li><strong>Authorities</strong> such as KRA, when the law requires it.</li>
</ul>

<h2>4. How long we keep it</h2>
<p>Order, payment and refund records are kept for 7 years for tax and accounting purposes. Account data is kept while your account is open. Security logs are kept for up to 90 days.</p>

<h2>5. Your rights</h2>
<p>You can ask to see the data we hold about you, correct it, have it deleted (where the law doesn't require us to keep it), object to or restrict how we use it, or receive it in a portable format. Email <a href="mailto:{{ $contact }}">{{ $contact }}</a> and we'll reply within 14 days. You can also complain to the <a href="https://www.odpc.go.ke" target="_blank" rel="noopener">Office of the Data Protection Commissioner</a>.</p>

<h2>6. Security</h2>
<p>Data travels over encrypted connections, passwords and gate PINs are stored scrambled, staff access is limited to what each role needs, and we keep regular backups.</p>

<h2>7. Children</h2>
<p>Buyers must be 18 or older, or buy with a parent's or guardian's permission. Events may set their own age limits.</p>

<h2>8. Changes and contact</h2>
<p>We'll post any changes here and update the date above. Questions: <a href="mailto:{{ $contact }}">{{ $contact }}</a>.</p>
@endsection
