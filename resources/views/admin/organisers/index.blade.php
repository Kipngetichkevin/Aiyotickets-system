{{-- resources/views/admin/organisers/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Organisers')

@php
    $badge = [
        'pending'   => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
        'approved'  => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
        'rejected'  => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
        'suspended' => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
    ];
    $small = 'h-8 w-full rounded-lg border border-gray-300 bg-transparent px-2 text-xs dark:border-gray-700 dark:text-white/90';
@endphp

@section('content')
<main>
    <div x-data="{ pageName: 'Organisers' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">

        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Organisers</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Check each applicant's ID and KRA PIN before approving. Approval lets them create events and experiences.</p>
                <span class="mt-2 flex flex-wrap gap-4 text-sm font-semibold">
                    <a href="{{ route('organisers.landing') }}" target="_blank" class="text-brand-600 hover:underline">Organiser landing page ↗</a>
                    <a href="{{ route('organisers.apply') }}" target="_blank" class="text-brand-600 hover:underline">Application form ↗</a>
                </span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'suspended' => 'Suspended', 'all' => 'All'] as $key => $text)
                    <a href="{{ route('admin.organisers.index', ['status' => $key, 'q' => $search ?: null]) }}"
                       class="rounded-lg border px-3 py-1.5 text-xs font-semibold {{ $status === $key ? 'border-brand-500 bg-brand-500 text-white' : 'border-gray-200 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200' }}">
                        {{ $text }}@if ($key !== 'all') ({{ $counts[$key] ?? 0 }})@endif
                    </a>
                @endforeach
            </div>
        </div>

        <form method="GET" class="mb-4 flex max-w-md gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <input name="q" value="{{ $search }}" placeholder="Name, email, phone or KRA PIN"
                   class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white/90">
            <button class="rounded-lg border border-gray-200 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200">Search</button>
        </form>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">{{ session('success') }}</div>
        @endif
        @if (session('error') || $errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">{{ session('error') ?? $errors->first() }}</div>
        @endif

        <div class="space-y-4">
            @forelse ($organisers as $m)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $m->name }}</h3>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge[$m->status] ?? $badge['rejected'] }}">{{ ucfirst($m->status) }}</span>
                                @if ($m->business_type)
                                    <span class="text-xs text-gray-500">{{ $m->business_type === 'company' ? 'Company' : 'Individual' }}</span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Account: {{ $m->user?->name }} ({{ $m->user?->email }}) · applied {{ $m->created_at?->format('d M Y') }}
                            </p>
                        </div>

                        <div class="flex min-w-[240px] flex-col gap-2">
                            @if (in_array($m->status, ['pending', 'rejected', 'suspended']))
                                <form method="POST" action="{{ route('admin.organisers.approve', $m) }}">@csrf
                                    <button class="w-full rounded-lg bg-brand-500 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-600">
                                        {{ $m->status === 'suspended' ? 'Reinstate' : 'Approve' }}
                                    </button>
                                </form>
                            @endif
                            @if ($m->status === 'pending')
                                <form method="POST" action="{{ route('admin.organisers.reject', $m) }}" class="flex gap-2">@csrf
                                    <input name="note" required maxlength="500" placeholder="Reason (sent to applicant)" class="{{ $small }}">
                                    <button class="shrink-0 rounded-lg border border-red-200 px-3 text-xs font-semibold text-red-600 hover:bg-red-50 dark:border-red-500/30">Reject</button>
                                </form>
                            @endif
                            @if ($m->status === 'approved')
                                <form method="POST" action="{{ route('admin.organisers.suspend', $m) }}" class="flex gap-2"
                                      onsubmit="return confirm('Suspend {{ addslashes($m->name) }}? They will lose access to their dashboard.')">@csrf
                                    <input name="note" required maxlength="500" placeholder="Reason (sent to organiser)" class="{{ $small }}">
                                    <button class="shrink-0 rounded-lg border border-red-200 px-3 text-xs font-semibold text-red-600 hover:bg-red-50 dark:border-red-500/30">Suspend</button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-2 text-sm sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ([
                            'Phone'       => $m->phone,
                            'Email'       => $m->email,
                            'Location'    => $m->location,
                            'Website'     => $m->website,
                            'ID / reg. no.' => $m->id_number,
                            'KRA PIN'     => $m->kra_pin,
                            'Payouts to'  => $m->payoutDestination(),
                        ] as $term => $value)
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">{{ $term }}</dt>
                                <dd class="break-words text-gray-800 dark:text-gray-200">
                                    @if ($term === 'Website' && $value)
                                        <a href="{{ $value }}" target="_blank" rel="noopener noreferrer" class="text-brand-600 hover:underline">{{ $value }}</a>
                                    @else
                                        {{ $value ?: '—' }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>

                    @if ($m->description)
                        <p class="mt-4 whitespace-pre-line rounded-xl bg-gray-50 p-3 text-sm text-gray-700 dark:bg-gray-800/50 dark:text-gray-300">{{ $m->description }}</p>
                    @endif

                    @if ($m->reviewed_at)
                        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                            {{ ucfirst($m->status) }} by {{ $m->reviewer?->name ?? 'admin' }}, {{ $m->reviewed_at->format('d M Y H:i') }}@if ($m->review_note) — “{{ $m->review_note }}”@endif
                        </p>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border-2 border-dashed border-gray-200 py-14 text-center text-gray-500 dark:border-gray-800 dark:text-gray-400">No organisers here.</div>
            @endforelse
        </div>

        <div class="mt-6">{{ $organisers->links() }}</div>
    </div>
</main>
@endsection
