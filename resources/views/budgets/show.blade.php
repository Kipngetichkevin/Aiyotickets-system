@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('budgets.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">&larr; Back to Budgets</a>
            <h1 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $budget->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ optional($budget->event)->name ?? '—' }} · {{ ucfirst($budget->category) }}
            </p>
        </div>

        {{-- Edit button — plain JS dispatch, no Alpine scope needed --}}
        <button type="button"
                onclick='window.dispatchEvent(new CustomEvent("open-budget-edit-modal", { detail: @json($budget) }))'
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
            Edit
        </button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-lg font-medium text-gray-800 dark:text-white/90 mb-4">Details</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Point of Contact</dt>
                        <dd class="mt-1 text-gray-800 dark:text-white/90">{{ $budget->point_of_contact ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Role</dt>
                        <dd class="mt-1 text-gray-800 dark:text-white/90">{{ $budget->role ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Phone</dt>
                        <dd class="mt-1 text-gray-800 dark:text-white/90">{{ $budget->phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="mt-1 text-gray-800 dark:text-white/90">{{ $budget->email ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500 dark:text-gray-400">Address</dt>
                        <dd class="mt-1 text-gray-800 dark:text-white/90">{{ $budget->address ?? '—' }}</dd>
                    </div>
                    @if($budget->contract_signature)
                        <div class="sm:col-span-2">
                            <dt class="text-gray-500 dark:text-gray-400">Contract Signature</dt>
                            <dd class="mt-1 text-gray-800 dark:text-white/90">{{ $budget->contract_signature }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-lg font-medium text-gray-800 dark:text-white/90 mb-4">Financials</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Cost</span>
                        <span class="font-medium">KES {{ number_format($budget->cost, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Paid</span>
                        <span class="font-medium text-green-600">KES {{ number_format($budget->paid_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between pt-3 border-t border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">Balance</span>
                        <span class="font-semibold {{ $budget->balance > 0 ? 'text-yellow-600' : 'text-green-600' }}">
                            KES {{ number_format($budget->balance, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status</span>
                        <span class="font-medium">{{ ucfirst($budget->status) }}</span>
                    </div>
                </div>
            </div>

            @if($budget->profile_pic)
                <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90 mb-4">Profile</h3>
                    <img src="{{ $budget->profile_pic_url }}" alt="" class="w-full rounded-lg">
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Include the modal so the Edit button above can open it --}}
@php
    $u = auth()->user();
    $a = $u->hasRole('admin');
    $evts = $a
        ? \App\Models\Event::orderBy('name')->get(['id', 'name'])
        : \App\Models\Event::where('user_id', $u->id)->orderBy('name')->get(['id', 'name']);
@endphp
@include('partials.modal.budget-modal', ['events' => $evts])

@endsection