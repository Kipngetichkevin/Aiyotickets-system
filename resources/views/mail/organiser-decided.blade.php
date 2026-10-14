<x-mail::message>
@if ($merchant->status === \App\Models\Merchant::APPROVED)
# You're approved 🎉

Hi {{ $merchant->user?->name ?? $merchant->name }}, **{{ $merchant->name }}** can now sell on {{ $appName }}. Create your first event or experience from your dashboard — buyers pay with M-Pesa and get their QR tickets instantly.

<x-mail::button :url="route('dashboard')">
Go to your dashboard
</x-mail::button>
@elseif ($merchant->status === \App\Models\Merchant::SUSPENDED)
# Your organiser account is suspended

Hi {{ $merchant->user?->name ?? $merchant->name }}, we've suspended **{{ $merchant->name }}** on {{ $appName }}.

<x-mail::panel>
{{ $merchant->review_note }}
</x-mail::panel>

Reply to this email if you'd like to discuss it.
@else
# About your application

Hi {{ $merchant->user?->name ?? $merchant->name }}, we couldn't approve **{{ $merchant->name }}** as an organiser yet.

<x-mail::panel>
{{ $merchant->review_note }}
</x-mail::panel>

You can update your details and apply again.

<x-mail::button :url="route('organisers.apply')">
Update application
</x-mail::button>
@endif

{{ $appName }}
</x-mail::message>
