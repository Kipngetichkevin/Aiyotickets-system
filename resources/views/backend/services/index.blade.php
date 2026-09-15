@include('backend.includes.header')
@include('backend.includes.Sidebar')

@php
    $presetEventId = request('event_id');
@endphp

<style>
    /* ============ PAGE CONTAINER ============ */
    .svc-main {
        margin-left: 300px !important;
        padding: 0 !important;
        min-height: 100vh !important;
        width: calc(100% - 300px) !important;
        box-sizing: border-box !important;
        background: #f9fafb;
    }

    .svc-content {
        padding: 32px 28px !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    /* ============ HEADER ============ */
    .svc-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-end !important;
        gap: 16px !important;
        margin-bottom: 28px !important;
        flex-wrap: wrap !important;
    }

    .svc-breadcrumb {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        font-size: 13px !important;
        color: #6b7280 !important;
        margin-bottom: 6px !important;
    }

    .svc-breadcrumb a {
        color: #6b7280 !important;
        text-decoration: none !important;
    }

    .svc-breadcrumb a:hover {
        color: #0284c7 !important;
    }

    .svc-title {
        font-size: 28px !important;
        font-weight: 700 !important;
        color: #111827 !important;
        margin: 0 !important;
        line-height: 1.2 !important;
    }

    .svc-subtitle {
        font-size: 15px !important;
        color: #6b7280 !important;
        margin: 6px 0 0 0 !important;
    }

    .btn-add-service {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        padding: 12px 20px !important;
        background: #0ea5e9 !important;
        color: #ffffff !important;
        font-size: 15px !important;
        font-weight: 500 !important;
        border: none !important;
        border-radius: 10px !important;
        cursor: pointer !important;
        text-decoration: none !important;
        transition: background 0.15s !important;
        white-space: nowrap !important;
    }

    .btn-add-service:hover {
        background: #0284c7 !important;
    }

    /* ============ FLASH ============ */
    .flash-success {
        padding: 16px 20px !important;
        background: #f0f9ff !important;
        border-left: 4px solid #0ea5e9 !important;
        color: #0c4a6e !important;
        border-radius: 10px !important;
        margin-bottom: 20px !important;
        font-size: 15px !important;
    }

    .flash-error {
        padding: 16px 20px !important;
        background: #fef2f2 !important;
        border-left: 4px solid #ef4444 !important;
        color: #991b1b !important;
        border-radius: 10px !important;
        margin-bottom: 20px !important;
        font-size: 15px !important;
    }

    .flash-info {
        padding: 16px 20px !important;
        background: #f0f9ff !important;
        border-left: 4px solid #0ea5e9 !important;
        color: #0c4a6e !important;
        border-radius: 10px !important;
        margin-bottom: 20px !important;
        font-size: 15px !important;
    }

    /* ============ STATS ============ */
    .svc-stats-grid {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 18px !important;
        margin-bottom: 28px !important;
        width: 100% !important;
    }

    .svc-stat-card {
        background: #f0f9ff !important;
        border: 1px solid #bae6fd !important;
        border-radius: 14px !important;
        padding: 22px 24px !important;
        box-sizing: border-box !important;
    }

    .svc-stat-label {
        font-size: 13px !important;
        font-weight: 700 !important;
        color: #0369a1 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        margin: 0 !important;
    }

    .svc-stat-value {
        font-size: 30px !important;
        font-weight: 700 !important;
        color: #0c4a6e !important;
        margin: 10px 0 0 0 !important;
        line-height: 1 !important;
    }

    /* ============ FILTER ============ */
    .svc-filter-card {
        background: #ffffff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 14px !important;
        padding: 14px !important;
        margin-bottom: 20px !important;
    }

    .svc-filter-form {
        display: flex !important;
        gap: 10px !important;
        align-items: center !important;
        flex-wrap: wrap !important;
    }

    .svc-filter-input {
        flex: 1 !important;
        min-width: 200px !important;
        padding: 11px 14px !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 10px !important;
        font-size: 15px !important;
        background: #f9fafb !important;
        outline: none !important;
        box-sizing: border-box !important;
    }

    .svc-filter-input:focus {
        border-color: #0ea5e9 !important;
        background: #ffffff !important;
    }

    .svc-filter-select {
        padding: 11px 14px !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 10px !important;
        font-size: 15px !important;
        background: #f9fafb !important;
        outline: none !important;
        cursor: pointer !important;
        box-sizing: border-box !important;
    }

    .svc-filter-btn {
        padding: 11px 24px !important;
        background: #0ea5e9 !important;
        color: white !important;
        border: none !important;
        border-radius: 10px !important;
        font-size: 15px !important;
        font-weight: 500 !important;
        cursor: pointer !important;
    }

    .svc-filter-btn:hover {
        background: #0284c7 !important;
    }

    .svc-filter-reset {
        padding: 11px 20px !important;
        background: #f3f4f6 !important;
        color: #374151 !important;
        border: none !important;
        border-radius: 10px !important;
        font-size: 15px !important;
        font-weight: 500 !important;
        text-decoration: none !important;
        display: inline-block !important;
    }

    .svc-filter-reset:hover {
        background: #e5e7eb !important;
    }

    /* ============ TABLE ============ */
    .svc-table-card {
        background: #ffffff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 14px !important;
        overflow: hidden !important;
    }

    .svc-table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .svc-table thead {
        background: #f0f9ff !important;
        border-bottom: 1px solid #bae6fd !important;
    }

    .svc-table th {
        padding: 14px 20px !important;
        text-align: left !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        color: #075985 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
    }

    .svc-table th:last-child {
        text-align: right !important;
    }

    .svc-table td {
        padding: 14px 20px !important;
        border-bottom: 1px solid #f3f4f6 !important;
        vertical-align: middle !important;
    }

    .svc-table tr:last-child td {
        border-bottom: none !important;
    }

    .svc-table tr:hover {
        background: #f9fafb !important;
    }

    .svc-empty {
        padding: 64px 28px !important;
        text-align: center !important;
    }

    .svc-empty-title {
        font-size: 16px !important;
        font-weight: 600 !important;
        color: #374151 !important;
        margin: 0 0 6px 0 !important;
    }

    .svc-empty-sub {
        font-size: 14px !important;
        color: #6b7280 !important;
        margin: 0 0 24px 0 !important;
    }

    /* ============ FORM ============ */
    .svc-form-label {
        display: block !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #374151 !important;
        margin-bottom: 6px !important;
    }

    .svc-form-label .req {
        color: #ef4444 !important;
    }

    .svc-form-input {
        width: 100% !important;
        padding: 11px 14px !important;
        border: 1px solid #d1d5db !important;
        border-radius: 8px !important;
        font-size: 15px !important;
        outline: none !important;
        box-sizing: border-box !important;
        background: #ffffff !important;
        transition: border-color 0.15s !important;
    }

    .svc-form-input:focus {
        border-color: #0ea5e9 !important;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1) !important;
    }

    .svc-form-section-title {
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #9ca3af !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        margin: 0 0 12px 0 !important;
    }

    .svc-more-toggle {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #0284c7 !important;
        background: none !important;
        border: none !important;
        padding: 8px 0 !important;
        cursor: pointer !important;
        margin-bottom: 8px !important;
    }

    .svc-more-toggle:hover {
        color: #0369a1 !important;
    }

    .svc-more-content {
        display: none !important;
        flex-direction: column !important;
        gap: 16px !important;
    }

    .svc-more-content.is-open {
        display: flex !important;
    }

    /* ============ MOBILE ============ */
    @media (max-width: 1023px) {
        .svc-main {
            margin-left: 0 !important;
            width: 100% !important;
        }
    }

    @media (max-width: 768px) {
        .svc-stats-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
</style>

<main class="svc-main">
    <div class="svc-content">

        {{-- ============ HEADER ============ --}}
        <div class="svc-header">
            <div>
                <nav class="svc-breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('backend.services.index') }}">Services</a>
                </nav>
                <h1 class="svc-title">Services</h1>
                <p class="svc-subtitle">
                    Contracts between events and payees — what was provided and for how much.
                </p>
            </div>

            <button type="button" onclick="openFormPanel()" class="btn-add-service">
                <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                </svg>
                Add Service
            </button>
        </div>

        {{-- ============ FLASH ============ --}}
        @if(session('success'))
            <div class="flash-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash-error">{{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="flash-info">{{ session('info') }}</div>
        @endif

        {{-- ============ STATS ============ --}}
        @php
            $totalServices = \App\Models\Service::count();
            $contractedValue = \App\Models\Service::whereIn('status', ['pending','approved','delivered'])->sum('total_amount');
            $deliveredValue = \App\Models\Service::where('status','delivered')->sum('total_amount');
            $paidValue = \App\Models\Payout::where('status','paid')->sum('amount');
        @endphp

        <div class="svc-stats-grid">
            <div class="svc-stat-card">
                <p class="svc-stat-label">Total Services</p>
                <p class="svc-stat-value">{{ $totalServices }}</p>
            </div>
            <div class="svc-stat-card">
                <p class="svc-stat-label">Contracted</p>
                <p class="svc-stat-value">KES {{ number_format($contractedValue, 0) }}</p>
            </div>
            <div class="svc-stat-card">
                <p class="svc-stat-label">Delivered</p>
                <p class="svc-stat-value">KES {{ number_format($deliveredValue, 0) }}</p>
            </div>
            <div class="svc-stat-card">
                <p class="svc-stat-label">Paid Out</p>
                <p class="svc-stat-value">KES {{ number_format($paidValue, 0) }}</p>
            </div>
        </div>

        {{-- ============ FILTER ============ --}}
        <div class="svc-filter-card">
            <form method="GET" class="svc-filter-form">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search description, invoice, payee, or event…"
                       class="svc-filter-input">

                <select name="event_id" class="svc-filter-select" style="min-width:200px;">
                    <option value="">All Events</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" @selected(request('event_id') == $event->id)>
                            {{ $event->name }}
                        </option>
                    @endforeach
                </select>

                <select name="payee_id" class="svc-filter-select" style="min-width:200px;">
                    <option value="">All Payees</option>
                    @foreach($payees as $payee)
                        <option value="{{ $payee->id }}" @selected(request('payee_id') == $payee->id)>
                            {{ $payee->name }} ({{ $payee->category }})
                        </option>
                    @endforeach
                </select>

                <select name="status" class="svc-filter-select" style="min-width:150px;">
                    <option value="">All Status</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    <option value="delivered" @selected(request('status') === 'delivered')>Delivered</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>

                <button type="submit" class="svc-filter-btn">Filter</button>

                @if(request()->hasAny(['search', 'event_id', 'payee_id', 'status']))
                    <a href="{{ route('backend.services.index') }}" class="svc-filter-reset">Reset</a>
                @endif
            </form>
        </div>

        {{-- ============ TABLE ============ --}}
        <div class="svc-table-card">
            @if($services->count())
                <div style="overflow-x: auto;">
                    <table class="svc-table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Event</th>
                                <th>Payee</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($services as $service)
                                <tr>
                                    <td>
                                        <button type="button" onclick="openShowModal('{{ $service->uuid }}')"
                                                style="font-size: 14px; font-weight: 600; color: #111827; background: none; border: none; padding: 0; cursor: pointer; text-align: left; display: block; max-width: 260px;">
                                            {{ Str::limit($service->description, 60) }}
                                        </button>
                                        <p style="font-size: 12px; color: #6b7280; margin: 2px 0 0 0;">
                                            {{ ucfirst($service->category) }}
                                            @if($service->invoice_number)
                                                · Invoice #{{ $service->invoice_number }}
                                            @endif
                                        </p>
                                    </td>
                                    <td>
                                        <span style="font-size: 13px; color: #111827; font-weight: 500;">
                                            {{ $service->event->name ?? '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 13px; color: #111827; font-weight: 500;">
                                            {{ $service->payee->name ?? '—' }}
                                        </span>
                                        <p style="font-size: 12px; color: #6b7280; margin: 0;">
                                            {{ ucfirst($service->payee->category ?? '') }}
                                        </p>
                                    </td>
                                    <td>
                                        <div style="font-size: 14px; color: #111827; font-weight: 600;">
                                            KES {{ number_format($service->total_amount, 0) }}
                                        </div>
                                        <div style="font-size: 12px; color: #6b7280;">
                                            {{ number_format($service->quantity, 0) }} × {{ number_format($service->unit_price, 0) }}
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusColor = match($service->status) {
                                                'pending' => 'background: #fef3c7; color: #92400e;',
                                                'approved' => 'background: #e0f2fe; color: #075985;',
                                                'delivered' => 'background: #d1fae5; color: #065f46;',
                                                'cancelled' => 'background: #fee2e2; color: #991b1b;',
                                                default => 'background: #f3f4f6; color: #6b7280;',
                                            };
                                        @endphp
                                        <span style="display: inline-block; padding: 3px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; {{ $statusColor }}">
                                            {{ ucfirst($service->status) }}
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: inline-flex; gap: 6px;">
                                            <button type="button" onclick="openShowModal('{{ $service->uuid }}')"
                                                    style="padding: 8px; background: none; border: none; cursor: pointer; border-radius: 8px;" title="View">
                                                <svg style="width: 18px; height: 18px; color: #9ca3af;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </button>
                                            <button type="button" onclick="openEditPanel('{{ $service->uuid }}')"
                                                    style="padding: 8px; background: none; border: none; cursor: pointer; border-radius: 8px;" title="Edit">
                                                <svg style="width: 18px; height: 18px; color: #9ca3af;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                    <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="svc-empty">
                    <p class="svc-empty-title">
                        @if(request()->hasAny(['search', 'event_id', 'payee_id', 'status']))
                            No services match your filters
                        @else
                            No services yet
                        @endif
                    </p>
                    <p class="svc-empty-sub">
                        @if(request()->hasAny(['search', 'event_id', 'payee_id', 'status']))
                            Try adjusting your search or <a href="{{ route('backend.services.index') }}" style="color:#0284c7;">reset filters</a>.
                        @else
                            Record a service agreed with a supplier, personnel, or creditor.
                        @endif
                    </p>
                    @unless(request()->hasAny(['search', 'event_id', 'payee_id', 'status']))
                        <button type="button" onclick="openFormPanel()" class="btn-add-service">
                            <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                            </svg>
                            Add Service
                        </button>
                    @endunless
                </div>
            @endif
        </div>

        @if($services->hasPages())
            <div style="margin-top: 24px;">{{ $services->withQueryString()->links() }}</div>
        @endif
    </div>
</main>

{{-- ================= RIGHT-SIDE DRAWER ================= --}}
<div id="serviceFormPanel"
     style="display: none; position: fixed; top: 0; right: 0; bottom: 0; width: 700px; max-width: 100vw; background: #ffffff; box-shadow: -10px 0 30px rgba(0,0,0,0.15); z-index: 200; overflow-y: auto; transform: translateX(100%); transition: transform 0.3s ease;">

    {{-- Drawer header --}}
    <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px 28px; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; background: #ffffff; z-index: 10;">
        <div>
            <h3 id="servicePanelTitle" style="font-size: 20px; font-weight: 700; color: #111827; margin: 0;">Add Service</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 4px 0 0 0;">Contract details between event and payee</p>
        </div>
        <button type="button" onclick="closeFormPanel()"
                style="padding: 8px; background: none; border: none; cursor: pointer; border-radius: 8px;">
            <svg style="width: 22px; height: 22px; color: #6b7280;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/>
            </svg>
        </button>
    </div>

    <form id="serviceForm" method="POST" action="{{ route('backend.services.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <input type="hidden" name="service_uuid" id="serviceUuid" value="{{ old('service_uuid') }}">

        <div style="padding: 28px;">

            @if ($errors->any())
                <div style="padding: 14px; background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 8px; margin-bottom: 24px;">
                    <p style="font-size: 14px; font-weight: 600; color: #991b1b; margin: 0 0 6px 0;">Please fix:</p>
                    <ul style="font-size: 13px; color: #b91c1c; margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- EVENT --}}
            <div style="margin-bottom: 20px;">
                <label for="eventSelect" class="svc-form-label">Event <span class="req">*</span></label>
                <select id="eventSelect" name="event_id" required class="svc-form-input">
                    <option value="">— Choose an event —</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" @selected(old('event_id') == $event->id)>
                            {{ $event->name }} — {{ \Carbon\Carbon::parse($event->date)->format('d M Y') }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- PAYEE --}}
            <div style="margin-bottom: 20px;">
                <label for="payeeSelect" class="svc-form-label">Payee <span class="req">*</span></label>
                <select id="payeeSelect" name="payee_id" required class="svc-form-input">
                    <option value="">— Choose a payee —</option>
                    @foreach($payees as $payee)
                        <option value="{{ $payee->id }}" @selected(old('payee_id') == $payee->id)>
                            {{ $payee->name }} ({{ ucfirst($payee->category) }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- DESCRIPTION --}}
            <div style="margin-bottom: 20px;">
                <label for="descriptionInput" class="svc-form-label">Description <span class="req">*</span></label>
                <textarea id="descriptionInput" name="description" required rows="2"
                          placeholder="e.g. Event photography — 4 hours including editing"
                          class="svc-form-input">{{ old('description') }}</textarea>
            </div>

            {{-- CATEGORY + STATUS --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label for="categorySelect" class="svc-form-label">Category <span class="req">*</span></label>
                    <select id="categorySelect" name="category" required class="svc-form-input">
                        <option value="">— Choose —</option>
                        <option value="equipment" @selected(old('category') === 'equipment')>Equipment</option>
                        <option value="labor" @selected(old('category') === 'labor')>Labor / Services</option>
                        <option value="food" @selected(old('category') === 'food')>Food & Beverage</option>
                        <option value="transport" @selected(old('category') === 'transport')>Transport</option>
                        <option value="venue" @selected(old('category') === 'venue')>Venue</option>
                        <option value="marketing" @selected(old('category') === 'marketing')>Marketing</option>
                        <option value="other" @selected(old('category') === 'other')>Other</option>
                    </select>
                </div>
                <div>
                    <label for="statusSelect" class="svc-form-label">Status <span class="req">*</span></label>
                    <select id="statusSelect" name="status" required class="svc-form-input">
                        <option value="pending" @selected(old('status', 'pending') === 'pending')>Pending</option>
                        <option value="approved" @selected(old('status') === 'approved')>Approved</option>
                        <option value="delivered" @selected(old('status') === 'delivered')>Delivered</option>
                        <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelled</option>
                    </select>
                </div>
            </div>

            {{-- AMOUNT — 3 columns --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label for="quantityInput" class="svc-form-label">Quantity <span class="req">*</span></label>
                    <input type="number" id="quantityInput" name="quantity" required step="0.01" min="0.01"
                           value="{{ old('quantity', 1) }}"
                           class="svc-form-input" oninput="recalcTotal()">
                </div>
                <div>
                    <label for="unitPriceInput" class="svc-form-label">Unit Price (KES) <span class="req">*</span></label>
                    <input type="number" id="unitPriceInput" name="unit_price" required step="0.01" min="0"
                           value="{{ old('unit_price', 0) }}"
                           class="svc-form-input" oninput="recalcTotal()">
                </div>
                <div>
                    <label for="totalDisplay" class="svc-form-label">Total (KES)</label>
                    <input type="text" id="totalDisplay" readonly
                           style="width: 100%; padding: 11px 14px; border: 1px solid #0ea5e9; border-radius: 8px; font-size: 15px; font-weight: 700; color: #0369a1; background: #f0f9ff; outline: none; box-sizing: border-box;"
                           value="0.00">
                </div>
            </div>

            {{-- DATES --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label for="contractedAtInput" class="svc-form-label">Contracted Date</label>
                    <input type="date" id="contractedAtInput" name="contracted_at"
                           value="{{ old('contracted_at') }}"
                           class="svc-form-input">
                </div>
                <div>
                    <label for="deliveredAtInput" class="svc-form-label">Delivered Date</label>
                    <input type="date" id="deliveredAtInput" name="delivered_at"
                           value="{{ old('delivered_at') }}"
                           class="svc-form-input">
                </div>
            </div>

            {{-- MORE OPTIONS (INVOICE + NOTES) --}}
            <div style="border-top: 1px solid #e5e7eb; padding-top: 20px; margin-top: 8px;">
                <button type="button" onclick="toggleMoreOptions()" class="svc-more-toggle">
                    <svg id="moreOptionsChevron" style="width: 14px; height: 14px; transition: transform 0.2s;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="m9 18 6-6-6-6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span id="moreOptionsLabel">Invoice & Notes (optional)</span>
                </button>

                <div id="moreOptionsContent" class="svc-more-content">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label for="invoiceNumberInput" class="svc-form-label">Invoice Number</label>
                            <input type="text" id="invoiceNumberInput" name="invoice_number"
                                   value="{{ old('invoice_number') }}"
                                   placeholder="e.g. INV-2025-042"
                                   class="svc-form-input">
                        </div>
                        <div>
                            <label for="invoiceDateInput" class="svc-form-label">Invoice Date</label>
                            <input type="date" id="invoiceDateInput" name="invoice_date"
                                   value="{{ old('invoice_date') }}"
                                   class="svc-form-input">
                        </div>
                    </div>

                    <div>
                        <label for="invoiceFileInput" class="svc-form-label">Attach Invoice (PDF / image)</label>
                        <input type="file" id="invoiceFileInput" name="invoice_file"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="svc-form-input">
                        <p style="font-size: 12px; color: #6b7280; margin: 6px 0 0 0;">
                            Max 5 MB. Accepted: PDF, JPG, PNG.
                        </p>
                        <div id="existingInvoiceBox" style="display: none; margin-top: 10px; padding: 10px 14px; background: #f0f9ff; border-radius: 8px; border: 1px solid #bae6fd;">
                            <p style="font-size: 13px; color: #0369a1; margin: 0;">
                                Current invoice:
                                <a id="existingInvoiceLink" href="#" target="_blank" style="color: #0284c7; text-decoration: underline;">View file</a>
                                <span style="color: #6b7280;"> — upload a new file to replace it</span>
                            </p>
                        </div>
                    </div>

                    <div>
                        <label for="notesInput" class="svc-form-label">Notes</label>
                        <textarea id="notesInput" name="notes" rows="3"
                                  placeholder="Any additional notes about this service…"
                                  class="svc-form-input">{{ old('notes') }}</textarea>
                    </div>

                </div>
            </div>

            <div style="height: 8px;"></div>
        </div>

        {{-- Footer actions --}}
        <div style="display: flex; justify-content: flex-end; gap: 10px; padding: 20px 28px; border-top: 1px solid #e5e7eb; background: #f9fafb; position: sticky; bottom: 0;">
            <button type="button" onclick="closeFormPanel()"
                    style="padding: 11px 20px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-weight: 500; color: #374151; background: white; cursor: pointer;">
                Cancel
            </button>
            <button type="submit" id="servicePanelSubmit"
                    style="padding: 11px 24px; background: #0ea5e9; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                Create Service
            </button>
        </div>
    </form>
</div>

{{-- Backdrop --}}
<div id="serviceBackdrop" onclick="closeFormPanel()"
     style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.2); z-index: 199;"></div>

{{-- ================= SHOW MODAL ================= --}}
<div id="showServiceModal" style="display: none; position: fixed; inset: 0; z-index: 300; overflow-y: auto;">
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5);" onclick="closeModal('showServiceModal')"></div>

    <div style="position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px;">
        <div style="position: relative; width: 100%; max-width: 680px; background: white; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">

            <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h3 id="showServiceTitle" style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Service Details</h3>
                <button type="button" onclick="closeModal('showServiceModal')" style="padding: 6px; background: none; border: none; cursor: pointer; border-radius: 6px;">
                    <svg style="width: 20px; height: 20px; color: #6b7280;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            <div style="padding: 24px; max-height: 65vh; overflow-y: auto;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px 24px; font-size: 14px;">
                    <div style="grid-column: 1 / -1;">
                        <span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Description</span>
                        <span id="show-svc-description" style="font-weight: 500; color: #111827;">—</span>
                    </div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Event</span><span id="show-svc-event" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Payee</span><span id="show-svc-payee" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Category</span><span id="show-svc-category" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Status</span><span id="show-svc-status" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Quantity</span><span id="show-svc-qty" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Unit Price</span><span id="show-svc-unit" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Total Amount</span><span id="show-svc-total" style="font-weight: 700; color: #0369a1;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Paid</span><span id="show-svc-paid" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Contracted</span><span id="show-svc-contracted" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Delivered</span><span id="show-svc-delivered" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Invoice #</span><span id="show-svc-invoice" style="font-weight: 500; color: #111827;">—</span></div>
                    <div style="grid-column: 1 / -1;">
                        <span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Invoice File</span>
                        <span id="show-svc-invoice-file" style="font-weight: 500; color: #111827;">—</span>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Notes</span>
                        <span id="show-svc-notes" style="font-weight: 500; color: #111827;">—</span>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-top: 1px solid #e5e7eb; background: #f9fafb; border-radius: 0 0 16px 16px;">
                <button type="button" onclick="deleteService()"
                        style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; color: #dc2626; background: none; border: none; cursor: pointer; border-radius: 8px; font-size: 14px; font-weight: 500;">
                    Delete
                </button>
                <div style="display: flex; gap: 8px;">
                    <button type="button" onclick="closeModal('showServiceModal')"
                            style="padding: 9px 18px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-weight: 500; color: #374151; background: white; cursor: pointer;">
                        Close
                    </button>
                    <button type="button" id="showEditBtn"
                            style="padding: 9px 18px; background: #0ea5e9; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                        Edit
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="deleteServiceForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
const allServices = @json(collect($services->items())->keyBy('uuid'));
let currentServiceUuid = null;

/* ================= TOTAL CALCULATION ================= */
function recalcTotal() {
    const qty = parseFloat(document.getElementById('quantityInput').value) || 0;
    const price = parseFloat(document.getElementById('unitPriceInput').value) || 0;
    document.getElementById('totalDisplay').value = (qty * price).toFixed(2);
}

/* ================= FORM PANEL ================= */
function openFormPanel() {
    const panel = document.getElementById('serviceFormPanel');
    const backdrop = document.getElementById('serviceBackdrop');
    panel.style.display = 'block';
    backdrop.style.display = 'block';
    requestAnimationFrame(() => {
        panel.style.transform = 'translateX(0)';
    });
    document.body.style.overflow = 'hidden';
}

function closeFormPanel() {
    const panel = document.getElementById('serviceFormPanel');
    const backdrop = document.getElementById('serviceBackdrop');
    panel.style.transform = 'translateX(100%)';
    setTimeout(() => {
        panel.style.display = 'none';
        backdrop.style.display = 'none';
    }, 300);
    document.body.style.overflow = '';
}

/* ================= MORE OPTIONS ================= */
function toggleMoreOptions() {
    const content = document.getElementById('moreOptionsContent');
    const chevron = document.getElementById('moreOptionsChevron');
    const isOpen = content.classList.toggle('is-open');
    if (chevron) chevron.style.transform = isOpen ? 'rotate(90deg)' : 'rotate(0deg)';
}

function resetMoreOptions() {
    const content = document.getElementById('moreOptionsContent');
    const chevron = document.getElementById('moreOptionsChevron');
    content.classList.remove('is-open');
    if (chevron) chevron.style.transform = 'rotate(0deg)';
}

/* ================= OPEN CREATE ================= */
function openCreatePanel() {
    const form = document.getElementById('serviceForm');
    form.reset();
    form.action = "{{ route('backend.services.store') }}";
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('serviceUuid').value = '';
    document.getElementById('servicePanelTitle').textContent = 'Add Service';
    document.getElementById('servicePanelSubmit').textContent = 'Create Service';

    // Defaults
    document.getElementById('quantityInput').value = '1';
    document.getElementById('unitPriceInput').value = '0';
    document.getElementById('statusSelect').value = 'pending';

    // Hide existing invoice box
    document.getElementById('existingInvoiceBox').style.display = 'none';

    // Reset more options
    resetMoreOptions();

    // Recalc
    recalcTotal();

    // If coming from an event page, pre-select the event
    const presetEvent = "{{ $presetEventId ?? '' }}";
    if (presetEvent) {
        document.getElementById('eventSelect').value = presetEvent;
    }

    openFormPanel();
}

/* ================= OPEN EDIT ================= */
function openEditPanel(uuid) {
    const service = allServices[uuid];
    if (!service) return;
    currentServiceUuid = uuid;
    const form = document.getElementById('serviceForm');
    form.reset();
    form.action = "/backend/services/" + uuid;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('serviceUuid').value = uuid;

    // Basic fields
    document.getElementById('eventSelect').value = service.event_id || '';
    document.getElementById('payeeSelect').value = service.payee_id || '';
    document.getElementById('descriptionInput').value = service.description || '';
    document.getElementById('categorySelect').value = service.category || '';
    document.getElementById('statusSelect').value = service.status || 'pending';
    document.getElementById('quantityInput').value = service.quantity || '1';
    document.getElementById('unitPriceInput').value = service.unit_price || '0';
    document.getElementById('contractedAtInput').value = service.contracted_at ? service.contracted_at.substring(0,10) : '';
    document.getElementById('deliveredAtInput').value = service.delivered_at ? service.delivered_at.substring(0,10) : '';
    document.getElementById('invoiceNumberInput').value = service.invoice_number || '';
    document.getElementById('invoiceDateInput').value = service.invoice_date ? service.invoice_date.substring(0,10) : '';
    document.getElementById('notesInput').value = service.notes || '';

    // Show existing invoice if any
    const invoiceBox = document.getElementById('existingInvoiceBox');
    const invoiceLink = document.getElementById('existingInvoiceLink');
    if (service.invoice_path) {
        invoiceBox.style.display = 'block';
        invoiceLink.href = '/storage/' + service.invoice_path;
    } else {
        invoiceBox.style.display = 'none';
    }

    // Open more options if invoice or notes present
    if (service.invoice_number || service.invoice_path || service.notes) {
        const content = document.getElementById('moreOptionsContent');
        const chevron = document.getElementById('moreOptionsChevron');
        content.classList.add('is-open');
        if (chevron) chevron.style.transform = 'rotate(90deg)';
    } else {
        resetMoreOptions();
    }

    // Recalc
    recalcTotal();

    document.getElementById('servicePanelTitle').textContent = 'Edit Service';
    document.getElementById('servicePanelSubmit').textContent = 'Save Changes';
    openFormPanel();
}

/* ================= SHOW MODAL ================= */
function showModal(id) { document.getElementById(id).style.display = 'block'; document.body.style.overflow = 'hidden'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; document.body.style.overflow = ''; }

function openShowModal(uuid) {
    const service = allServices[uuid];
    if (!service) return;
    currentServiceUuid = uuid;

    document.getElementById('showServiceTitle').textContent = service.description ? 'Service Details' : 'Service';

    const set = (id, val) => { document.getElementById(id).textContent = (val != null && val !== '') ? val : '—'; };
    set('show-svc-description', service.description);
    set('show-svc-event', service.event ? service.event.name : '—');
    set('show-svc-payee', service.payee ? service.payee.name : '—');
    set('show-svc-category', service.category ? service.category.charAt(0).toUpperCase() + service.category.slice(1) : '');
    set('show-svc-status', service.status ? service.status.charAt(0).toUpperCase() + service.status.slice(1) : '');
    set('show-svc-qty', service.quantity);
    set('show-svc-unit', 'KES ' + Number(service.unit_price).toLocaleString());
    set('show-svc-total', 'KES ' + Number(service.total_amount).toLocaleString());
    set('show-svc-paid', 'KES ' + Number(service.total_paid || 0).toLocaleString());
    set('show-svc-contracted', service.contracted_at ? service.contracted_at.substring(0,10) : '');
    set('show-svc-delivered', service.delivered_at ? service.delivered_at.substring(0,10) : '');
    set('show-svc-invoice', service.invoice_number);
    set('show-svc-notes', service.notes);

    // Invoice file link
    const fileSpan = document.getElementById('show-svc-invoice-file');
    if (service.invoice_path) {
        fileSpan.innerHTML = '<a href="/storage/' + service.invoice_path + '" target="_blank" style="color: #0284c7; text-decoration: underline;">View invoice</a>';
    } else {
        fileSpan.textContent = '—';
    }

    document.getElementById('showEditBtn').onclick = () => { closeModal('showServiceModal'); openEditPanel(uuid); };
    showModal('showServiceModal');
}

/* ================= DELETE ================= */
function deleteService() {
    if (!currentServiceUuid) return;
    if (!confirm('Delete this service? This cannot be undone.')) return;
    const form = document.getElementById('deleteServiceForm');
    form.action = "/backend/services/" + currentServiceUuid;
    form.submit();
}

/* ================= GLOBAL ================= */
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeFormPanel();
        closeModal('showServiceModal');
    }
});

document.addEventListener('DOMContentLoaded', () => {
    recalcTotal();
});

@if ($errors->any())
document.addEventListener('DOMContentLoaded', () => {
    const method = "{{ old('_method', 'POST') }}";
    if (method === 'PUT' && "{{ old('service_uuid') }}") openEditPanel("{{ old('service_uuid') }}");
    else openCreatePanel();
});
@endIf
</script>

@include('backend.includes.Footer')