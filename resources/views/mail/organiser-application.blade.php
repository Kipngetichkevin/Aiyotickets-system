<x-mail::message>
# New organiser application

**{{ $merchant->name }}** ({{ $merchant->business_type === 'company' ? 'company' : 'individual' }}) has applied to sell on {{ $appName }}.

<x-mail::panel>
**Contact:** {{ $merchant->email }} · {{ $merchant->phone }}
**Location:** {{ $merchant->location }}
**KRA PIN:** {{ $merchant->kra_pin }}
**Payouts to:** {{ $merchant->payoutDestination() }}
</x-mail::panel>

{{ $merchant->description }}

<x-mail::button :url="route('admin.organisers.index')">
Review application
</x-mail::button>

{{ $appName }}
</x-mail::message>
