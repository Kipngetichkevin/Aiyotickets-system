@include('backend.includes.header')
@include('backend.includes.Sidebar')

@php
    $presetCategory = request('category');
    $validCategories = ['supplier', 'personnel', 'creditor', 'debtor', 'other'];
    $presetCategory = in_array($presetCategory, $validCategories) ? $presetCategory : null;
@endphp

<style>
    /* ============ PAGE CONTAINER — offset by 300px sidebar ============ */
    .payee-main {
        margin-left: 300px !important;
        padding: 0 !important;
        min-height: 100vh !important;
        width: calc(100% - 300px) !important;
        box-sizing: border-box !important;
        background: #f9fafb;
    }

    .payee-content {
        padding: 32px 28px !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    /* ============ HEADER ROW ============ */
    .payee-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-end !important;
        gap: 16px !important;
        margin-bottom: 28px !important;
        flex-wrap: wrap !important;
    }

    .payee-breadcrumb {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        font-size: 13px !important;
        color: #6b7280 !important;
        margin-bottom: 6px !important;
    }

    .payee-breadcrumb a {
        color: #6b7280 !important;
        text-decoration: none !important;
    }

    .payee-breadcrumb a:hover {
        color: #0284c7 !important;
    }

    .payee-title {
        font-size: 28px !important;
        font-weight: 700 !important;
        color: #111827 !important;
        margin: 0 !important;
        line-height: 1.2 !important;
    }

    .payee-subtitle {
        font-size: 15px !important;
        color: #6b7280 !important;
        margin: 6px 0 0 0 !important;
    }

    .btn-add-payee {
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

    .btn-add-payee:hover {
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

    /* ============ STATS GRID ============ */
    .stats-grid {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 18px !important;
        margin-bottom: 28px !important;
        width: 100% !important;
    }

    .stat-card {
        background: #f0f9ff !important;
        border: 1px solid #bae6fd !important;
        border-radius: 14px !important;
        padding: 22px 24px !important;
        box-sizing: border-box !important;
        transition: box-shadow 0.15s !important;
    }

    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.1) !important;
    }

    .stat-label {
        font-size: 13px !important;
        font-weight: 700 !important;
        color: #0369a1 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        margin: 0 !important;
    }

    .stat-value {
        font-size: 36px !important;
        font-weight: 700 !important;
        color: #0c4a6e !important;
        margin: 10px 0 0 0 !important;
        line-height: 1 !important;
    }

    /* ============ FILTER BAR ============ */
    .filter-card {
        background: #ffffff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 14px !important;
        padding: 14px !important;
        margin-bottom: 20px !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04) !important;
    }

    .filter-form {
        display: flex !important;
        gap: 10px !important;
        align-items: center !important;
        flex-wrap: wrap !important;
    }

    .filter-input {
        flex: 1 !important;
        min-width: 220px !important;
        padding: 11px 14px !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 10px !important;
        font-size: 15px !important;
        background: #f9fafb !important;
        outline: none !important;
        box-sizing: border-box !important;
    }

    .filter-input:focus {
        border-color: #0ea5e9 !important;
        background: #ffffff !important;
    }

    .filter-select {
        padding: 11px 14px !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 10px !important;
        font-size: 15px !important;
        background: #f9fafb !important;
        outline: none !important;
        cursor: pointer !important;
        box-sizing: border-box !important;
    }

    .filter-btn {
        padding: 11px 24px !important;
        background: #0ea5e9 !important;
        color: white !important;
        border: none !important;
        border-radius: 10px !important;
        font-size: 15px !important;
        font-weight: 500 !important;
        cursor: pointer !important;
    }

    .filter-btn:hover {
        background: #0284c7 !important;
    }

    .filter-reset {
        padding: 11px 20px !important;
        background: #f3f4f6 !important;
        color: #374151 !important;
        border: none !important;
        border-radius: 10px !important;
        font-size: 15px !important;
        font-weight: 500 !important;
        text-decoration: none !important;
        display: inline-block !important;
        cursor: pointer !important;
    }

    .filter-reset:hover {
        background: #e5e7eb !important;
    }

    /* ============ TABLE ============ */
    .table-card {
        background: #ffffff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 14px !important;
        overflow: hidden !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04) !important;
    }

    .payee-table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .payee-table thead {
        background: #f0f9ff !important;
        border-bottom: 1px solid #bae6fd !important;
    }

    .payee-table th {
        padding: 14px 20px !important;
        text-align: left !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        color: #075985 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
    }

    .payee-table th:last-child {
        text-align: right !important;
    }

    .payee-table td {
        padding: 14px 20px !important;
        border-bottom: 1px solid #f3f4f6 !important;
        vertical-align: middle !important;
    }

    .payee-table tr:last-child td {
        border-bottom: none !important;
    }

    .payee-table tr:hover {
        background: #f9fafb !important;
    }

    .empty-state {
        padding: 64px 28px !important;
        text-align: center !important;
    }

    .empty-title {
        font-size: 16px !important;
        font-weight: 600 !important;
        color: #374151 !important;
        margin: 0 0 6px 0 !important;
    }

    .empty-sub {
        font-size: 14px !important;
        color: #6b7280 !important;
        margin: 0 0 24px 0 !important;
    }

    /* ============ FORM STYLES ============ */
    .form-label {
        display: block !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #374151 !important;
        margin-bottom: 6px !important;
    }

    .form-label .req {
        color: #ef4444 !important;
    }

    .form-input {
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

    .form-input:focus {
        border-color: #0ea5e9 !important;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1) !important;
    }

    .form-section-title {
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #9ca3af !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        margin: 0 0 12px 0 !important;
    }

    .more-options-toggle {
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

    .more-options-toggle:hover {
        color: #0369a1 !important;
    }

    .more-options-content {
        display: none !important;
        flex-direction: column !important;
        gap: 16px !important;
    }

    .more-options-content.is-open {
        display: flex !important;
    }

    /* ============ MOBILE ============ */
    @media (max-width: 1023px) {
        .payee-main {
            margin-left: 0 !important;
            width: 100% !important;
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
</style>

<main class="payee-main">
    <div class="payee-content">

        {{-- ============ HEADER ============ --}}
        <div class="payee-header">
            <div>
                <nav class="payee-breadcrumb">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('backend.payees.index') }}">Payees</a>
                    @if(request('category'))
                        <span>/</span>
                        <span style="color:#374151; font-weight:500;">{{ ucfirst(request('category')) }}s</span>
                    @endif
                </nav>
                <h1 class="payee-title">
                    {{ request('category') ? ucfirst(request('category')) . 's' : 'All Payees' }}
                </h1>
                <p class="payee-subtitle">
                    Suppliers, personnel, creditors, and other payees.
                </p>
            </div>

            <button type="button" onclick="openFormPanel()" class="btn-add-payee">
                <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                </svg>
                Add Payee
            </button>
        </div>

        {{-- ============ FLASH ============ --}}
        @if(session('success'))
            <div class="flash-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash-error">{{ session('error') }}</div>
        @endif

        {{-- ============ STATS ============ --}}
        @php
            $total     = \App\Models\Payee::count();
            $suppliers = \App\Models\Payee::where('category','supplier')->count();
            $personnel = \App\Models\Payee::where('category','personnel')->count();
            $creditors = \App\Models\Payee::where('category','creditor')->count();
        @endphp

        <div class="stats-grid">
            <div class="stat-card">
                <p class="stat-label">Total</p>
                <p class="stat-value">{{ $total }}</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Suppliers</p>
                <p class="stat-value">{{ $suppliers }}</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Personnel</p>
                <p class="stat-value">{{ $personnel }}</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Creditors</p>
                <p class="stat-value">{{ $creditors }}</p>
            </div>
        </div>

        {{-- ============ FILTER ============ --}}
        <div class="filter-card">
            <form method="GET" class="filter-form">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name, phone, or email…"
                       class="filter-input">

                <select name="category" class="filter-select" style="min-width:160px;">
                    <option value="">All Categories</option>
                    <option value="supplier" @selected(request('category') === 'supplier')>Suppliers</option>
                    <option value="personnel" @selected(request('category') === 'personnel')>Personnel</option>
                    <option value="creditor" @selected(request('category') === 'creditor')>Creditors</option>
                    <option value="debtor" @selected(request('category') === 'debtor')>Debtors</option>
                    <option value="other" @selected(request('category') === 'other')>Other</option>
                </select>

                <select name="status" class="filter-select" style="min-width:140px;">
                    <option value="">All Status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>

                <button type="submit" class="filter-btn">Filter</button>

                @if(request()->hasAny(['search', 'category', 'status']))
                    <a href="{{ route('backend.payees.index') }}" class="filter-reset">Reset</a>
                @endif
            </form>
        </div>

        {{-- ============ TABLE ============ --}}
        <div class="table-card">
            @if($payees->count())
                <div style="overflow-x: auto;">
                    <table class="payee-table">
                        <thead>
                            <tr>
                                <th>Payee</th>
                                <th>Category</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payees as $payee)
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 28px; height: 28px; border-radius: 9999px; background: #0ea5e9; color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0;">
                                                {{ strtoupper(substr($payee->name, 0, 1)) }}
                                            </div>
                                            <div style="min-width: 0;">
                                                <button type="button" onclick="openShowModal('{{ $payee->uuid }}')"
                                                        style="font-size: 15px; font-weight: 600; color: #111827; background: none; border: none; padding: 0; cursor: pointer; text-align: left; display: block;">
                                                    {{ $payee->name }}
                                                </button>
                                                @if($payee->type)
                                                    <p style="font-size: 13px; color: #6b7280; margin: 0;">{{ $payee->type }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="display: inline-block; padding: 3px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; background: #e0f2fe; color: #075985;">
                                            {{ ucfirst($payee->category) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($payee->phone)
                                            <div style="font-size: 14px; color: #111827; font-weight: 500;">{{ $payee->phone }}</div>
                                        @endif
                                        @if($payee->email)
                                            <div style="font-size: 13px; color: #6b7280;">{{ $payee->email }}</div>
                                        @endif
                                        @if(!$payee->phone && !$payee->email)
                                            <span style="font-size: 13px; color: #9ca3af;">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 8px; font-size: 12px; font-weight: 600;
                                            {{ $payee->status === 'active' ? 'background: #e0f2fe; color: #075985;' : 'background: #f3f4f6; color: #6b7280;' }}">
                                            <span style="width: 7px; height: 7px; border-radius: 9999px; {{ $payee->status === 'active' ? 'background: #0ea5e9;' : 'background: #9ca3af;' }}"></span>
                                            {{ ucfirst($payee->status) }}
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: inline-flex; gap: 6px;">
                                            <button type="button" onclick="openShowModal('{{ $payee->uuid }}')"
                                                    style="padding: 8px; background: none; border: none; cursor: pointer; border-radius: 8px;" title="View">
                                                <svg style="width: 18px; height: 18px; color: #9ca3af;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </button>
                                            <button type="button" onclick="openEditPanel('{{ $payee->uuid }}')"
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
                <div class="empty-state">
                    <p class="empty-title">
                        @if(request()->hasAny(['search', 'category', 'status']))
                            No payees match your filters
                        @else
                            No payees yet
                        @endif
                    </p>
                    <p class="empty-sub">
                        @if(request()->hasAny(['search', 'category', 'status']))
                            Try adjusting your search or <a href="{{ route('backend.payees.index') }}" style="color:#0284c7;">reset filters</a>.
                        @else
                            Add your first supplier, personnel, or creditor.
                        @endif
                    </p>
                    @unless(request()->hasAny(['search', 'category', 'status']))
                        <button type="button" onclick="openFormPanel()" class="btn-add-payee">
                            <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                            </svg>
                            Add Payee
                        </button>
                    @endunless
                </div>
            @endif
        </div>

        @if($payees->hasPages())
            <div style="margin-top: 24px;">{{ $payees->withQueryString()->links() }}</div>
        @endif
    </div>
</main>

{{-- ================= RIGHT-SIDE DRAWER ================= --}}
<div id="payeeFormPanel"
     style="display: none; position: fixed; top: 0; right: 0; bottom: 0; width: 640px; max-width: 100vw; background: #ffffff; box-shadow: -10px 0 30px rgba(0,0,0,0.15); z-index: 200; overflow-y: auto; transform: translateX(100%); transition: transform 0.3s ease;">

    {{-- Drawer header --}}
    <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px 28px; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; background: #ffffff; z-index: 10;">
        <div>
            <h3 id="payeePanelTitle" style="font-size: 20px; font-weight: 700; color: #111827; margin: 0;">Add Payee</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 4px 0 0 0;">Fill in the essential details</p>
        </div>
        <button type="button" onclick="closeFormPanel()"
                style="padding: 8px; background: none; border: none; cursor: pointer; border-radius: 8px;">
            <svg style="width: 22px; height: 22px; color: #6b7280;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/>
            </svg>
        </button>
    </div>

    <form id="payeeForm" method="POST" action="{{ route('backend.payees.store') }}">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <input type="hidden" name="payee_uuid" id="payeeUuid" value="{{ old('payee_uuid') }}">

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

            {{-- ============ CATEGORY ============ --}}
            <div style="margin-bottom: 24px;">

                @if($presetCategory)
                    <div id="categoryLockedView">
                        <span class="form-label">Category</span>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border: 2px solid #0ea5e9; border-radius: 10px; background: #f0f9ff;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <svg style="width: 18px; height: 18px; color: #0ea5e9;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M9 12l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="12" r="9"/>
                                </svg>
                                <span style="font-size: 15px; font-weight: 600; color: #0369a1; text-transform: capitalize;">
                                    {{ $presetCategory }}
                                </span>
                            </div>
                            <button type="button" onclick="unlockCategory()"
                                    style="font-size: 13px; color: #0284c7; background: none; border: none; cursor: pointer; text-decoration: underline; font-weight: 500;">
                                Change
                            </button>
                        </div>
                        <input type="hidden" name="category" id="categoryHidden" value="{{ $presetCategory }}">
                    </div>

                    <div id="categoryPickerView" style="display: none;">
                        <span class="form-label">Category <span class="req">*</span></span>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                            @foreach(['supplier' => 'Supplier', 'personnel' => 'Personnel', 'creditor' => 'Creditor', 'debtor' => 'Debtor', 'other' => 'Other'] as $val => $lbl)
                                <label style="cursor: pointer;">
                                    <input type="radio" name="category" value="{{ $val }}" style="position: absolute; opacity: 0; width: 0; height: 0;" @checked($presetCategory === $val)>
                                    <div onclick="selectCategory(this)" data-value="{{ $val }}"
                                         class="cat-option"
                                         style="text-align: center; padding: 12px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; font-weight: 500; color: #6b7280; cursor: pointer; transition: all 0.15s;">
                                        {{ $lbl }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                @else
                    <span class="form-label">Category <span class="req">*</span></span>
                    <div id="categoryPickerView">
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                            @foreach(['supplier' => 'Supplier', 'personnel' => 'Personnel', 'creditor' => 'Creditor', 'debtor' => 'Debtor', 'other' => 'Other'] as $val => $lbl)
                                <label style="cursor: pointer;">
                                    <input type="radio" name="category" value="{{ $val }}" style="position: absolute; opacity: 0; width: 0; height: 0;" @checked(old('category', 'supplier') === $val)>
                                    <div onclick="selectCategory(this)" data-value="{{ $val }}"
                                         class="cat-option"
                                         style="text-align: center; padding: 12px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; font-weight: 500; color: #6b7280; cursor: pointer; transition: all 0.15s;">
                                        {{ $lbl }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            {{-- ============ NAME ============ --}}
            <div style="margin-bottom: 20px;">
                <label for="nameInput" class="form-label">Name <span class="req">*</span></label>
                <input type="text" id="nameInput" name="name" value="{{ old('name') }}" required maxlength="255"
                       placeholder="e.g. Pulser Photography"
                       class="form-input">
            </div>

            {{-- ============ TYPE / ROLE ============ --}}
            <div style="margin-bottom: 20px;">
                <label for="typeInput" class="form-label">Type / Role</label>
                <input type="text" id="typeInput" name="type" value="{{ old('type') }}" maxlength="255"
                       placeholder="e.g. Photographer, Tent Vendor, MC"
                       class="form-input">
            </div>

            {{-- ============ CONTACT ============ --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label for="phoneInput" class="form-label">Phone</label>
                    <input type="text" id="phoneInput" name="phone" value="{{ old('phone') }}" maxlength="20"
                           placeholder="0712345678"
                           class="form-input">
                </div>
                <div>
                    <label for="emailInput" class="form-label">Email</label>
                    <input type="email" id="emailInput" name="email" value="{{ old('email') }}" maxlength="255"
                           placeholder="email@example.com"
                           class="form-input">
                </div>
            </div>

            {{-- ============ PAYMENT METHOD DROPDOWN ============ --}}
            <div style="margin-bottom: 20px;">
                <label for="paymentMethodSelect" class="form-label">Preferred Payment Method</label>
                <select id="paymentMethodSelect" class="form-input" onchange="onPaymentMethodChange()">
                    <option value="mpesa" @selected(old('payment_method', 'mpesa') === 'mpesa')>M-Pesa</option>
                    <option value="bank" @selected(old('payment_method') === 'bank')>Bank Transfer</option>
                    <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                    <option value="cheque" @selected(old('payment_method') === 'cheque')>Cheque</option>
                </select>
            </div>

            {{-- Payment fields that show/hide based on the dropdown --}}
            <div id="paymentFieldsContainer" style="margin-bottom: 20px;">

                {{-- M-PESA fields --}}
                <div id="paymentFields_mpesa">
                    <label for="mpesaInput" class="form-label">M-Pesa Number</label>
                    <input type="text" id="mpesaInput" name="mpesa_number" value="{{ old('mpesa_number') }}" maxlength="20"
                           placeholder="254712345678"
                           class="form-input">
                    <p style="font-size: 12px; color: #6b7280; margin: 6px 0 0 0;">
                        Format: 254XXXXXXXXX (12 digits starting with 254)
                    </p>
                </div>

                {{-- BANK fields --}}
                <div id="paymentFields_bank" style="display: none;">
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div>
                            <label for="bankNameInput" class="form-label">Bank Name</label>
                            <input type="text" id="bankNameInput" name="bank_name" value="{{ old('bank_name') }}" maxlength="255"
                                   placeholder="e.g. Equity Bank, KCB, Co-op"
                                   class="form-input">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div>
                                <label for="bankAccountInput" class="form-label">Account Number</label>
                                <input type="text" id="bankAccountInput" name="bank_account" value="{{ old('bank_account') }}" maxlength="255"
                                       placeholder="0123456789"
                                       class="form-input">
                            </div>
                            <div>
                                <label for="bankBranchInput" class="form-label">Branch</label>
                                <input type="text" id="bankBranchInput" name="bank_branch" value="{{ old('bank_branch') }}" maxlength="255"
                                       placeholder="e.g. Westlands"
                                       class="form-input">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CASH --}}
                <div id="paymentFields_cash" style="display: none;">
                    <div style="padding: 14px 16px; background: #f0f9ff; border-radius: 8px; border: 1px solid #bae6fd;">
                        <p style="font-size: 13px; color: #0369a1; margin: 0;">
                            No payment details needed for cash. You'll record cash payments manually when paying this payee.
                        </p>
                    </div>
                </div>

                {{-- CHEQUE --}}
                <div id="paymentFields_cheque" style="display: none;">
                    <div style="padding: 14px 16px; background: #f0f9ff; border-radius: 8px; border: 1px solid #bae6fd;">
                        <p style="font-size: 13px; color: #0369a1; margin: 0;">
                            No payment details needed for cheque. You'll record cheque payments manually when paying this payee.
                        </p>
                    </div>
                </div>

            </div>

            {{-- ============ STATUS ============ --}}
            <div style="margin-bottom: 20px;">
                <label for="statusSelect" class="form-label">Status</label>
                <select id="statusSelect" name="status" class="form-input">
                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                </select>
            </div>

            {{-- ============ MORE OPTIONS (COLLAPSIBLE) ============ --}}
            <div style="border-top: 1px solid #e5e7eb; padding-top: 20px; margin-top: 8px;">
                <button type="button" onclick="toggleMoreOptions()" class="more-options-toggle">
                    <svg id="moreOptionsChevron" style="width: 14px; height: 14px; transition: transform 0.2s;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="m9 18 6-6-6-6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span id="moreOptionsLabel">More options</span>
                </button>

                <div id="moreOptionsContent" class="more-options-content">

                    <div>
                        <label for="pocInput" class="form-label">Contact Person</label>
                        <input type="text" id="pocInput" name="point_of_contact" value="{{ old('point_of_contact') }}" maxlength="255"
                               placeholder="The person we usually talk to"
                               class="form-input">
                    </div>

                    <div>
                        <label for="addressInput" class="form-label">Address</label>
                        <textarea id="addressInput" name="address" rows="2"
                                  placeholder="Physical or postal address"
                                  class="form-input">{{ old('address') }}</textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label for="idNumberInput" class="form-label">ID Number</label>
                            <input type="text" id="idNumberInput" name="id_number" value="{{ old('id_number') }}" maxlength="255"
                                   placeholder="National ID / Passport"
                                   class="form-input">
                        </div>
                        <div>
                            <label for="kraPinInput" class="form-label">KRA PIN</label>
                            <input type="text" id="kraPinInput" name="kra_pin" value="{{ old('kra_pin') }}" maxlength="255"
                                   placeholder="e.g. A123456789Z"
                                   class="form-input">
                        </div>
                    </div>

                    <div>
                        <label for="notesInput" class="form-label">Notes</label>
                        <textarea id="notesInput" name="notes" rows="3"
                                  placeholder="Any extra information about this payee…"
                                  class="form-input">{{ old('notes') }}</textarea>
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
            <button type="submit" id="payeePanelSubmit"
                    style="padding: 11px 24px; background: #0ea5e9; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                Create Payee
            </button>
        </div>
    </form>
</div>

{{-- Backdrop --}}
<div id="payeeBackdrop" onclick="closeFormPanel()"
     style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.2); z-index: 199;"></div>

{{-- ================= SHOW MODAL ================= --}}
<div id="showPayeeModal" style="display: none; position: fixed; inset: 0; z-index: 300; overflow-y: auto;">
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5);" onclick="closeModal('showPayeeModal')"></div>

    <div style="position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px;">
        <div style="position: relative; width: 100%; max-width: 640px; background: white; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">

            <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h3 id="showPayeeName" style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Payee Details</h3>
                <button type="button" onclick="closeModal('showPayeeModal')" style="padding: 6px; background: none; border: none; cursor: pointer; border-radius: 6px;">
                    <svg style="width: 20px; height: 20px; color: #6b7280;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            <div style="padding: 24px; max-height: 65vh; overflow-y: auto;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px 24px; font-size: 14px;">
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Category</span><span id="show-payee-category" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Type</span><span id="show-payee-type" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Contact Person</span><span id="show-payee-poc" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Phone</span><span id="show-payee-phone" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Email</span><span id="show-payee-email" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">M-Pesa</span><span id="show-payee-mpesa" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Bank</span><span id="show-payee-bank" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Bank Account</span><span id="show-payee-account" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Bank Branch</span><span id="show-payee-branch" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">ID Number</span><span id="show-payee-idnum" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">KRA PIN</span><span id="show-payee-kra" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Status</span><span id="show-payee-status" style="font-weight: 500; color: #111827;">—</span></div>
                    <div style="grid-column: 1 / -1;"><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Address</span><span id="show-payee-address" style="font-weight: 500; color: #111827;">—</span></div>
                    <div style="grid-column: 1 / -1;"><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Notes</span><span id="show-payee-notes" style="font-weight: 500; color: #111827;">—</span></div>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-top: 1px solid #e5e7eb; background: #f9fafb; border-radius: 0 0 16px 16px;">
                <button type="button" onclick="deletePayee()"
                        style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; color: #dc2626; background: none; border: none; cursor: pointer; border-radius: 8px; font-size: 14px; font-weight: 500;">
                    Delete
                </button>
                <div style="display: flex; gap: 8px;">
                    <button type="button" onclick="closeModal('showPayeeModal')"
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

<form id="deletePayeeForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
const allPayees = @json(collect($payees->items())->keyBy('uuid'));
let currentPayeeUuid = null;
const presetCategory = "{{ $presetCategory }}";

/* ================= CATEGORY SELECTION ================= */
function selectCategory(el) {
    document.querySelectorAll('.cat-option').forEach(function (opt) {
        opt.style.background = 'transparent';
        opt.style.color = '#6b7280';
        opt.style.borderColor = '#e5e7eb';
    });
    el.style.background = '#0ea5e9';
    el.style.color = '#ffffff';
    el.style.borderColor = '#0ea5e9';
}

/* ================= FORM PANEL ================= */
function openFormPanel() {
    const panel = document.getElementById('payeeFormPanel');
    const backdrop = document.getElementById('payeeBackdrop');
    panel.style.display = 'block';
    backdrop.style.display = 'block';
    requestAnimationFrame(() => {
        panel.style.transform = 'translateX(0)';
    });
    document.body.style.overflow = 'hidden';
}

function closeFormPanel() {
    const panel = document.getElementById('payeeFormPanel');
    const backdrop = document.getElementById('payeeBackdrop');
    panel.style.transform = 'translateX(100%)';
    setTimeout(() => {
        panel.style.display = 'none';
        backdrop.style.display = 'none';
    }, 300);
    document.body.style.overflow = '';
}

/* ================= PAYMENT METHOD DROPDOWN ================= */
function onPaymentMethodChange() {
    const method = document.getElementById('paymentMethodSelect').value;

    // Hide all first
    ['mpesa', 'bank', 'cash', 'cheque'].forEach(function (m) {
        const el = document.getElementById('paymentFields_' + m);
        if (el) el.style.display = 'none';
    });

    // Show the selected one
    const selected = document.getElementById('paymentFields_' + method);
    if (selected) selected.style.display = 'block';

    // Clear hidden payment fields so we don't save stale data
    if (method !== 'mpesa') {
        document.getElementById('mpesaInput').value = '';
    }
    if (method !== 'bank') {
        document.getElementById('bankNameInput').value = '';
        document.getElementById('bankAccountInput').value = '';
        document.getElementById('bankBranchInput').value = '';
    }
}

/* ================= MORE OPTIONS TOGGLE ================= */
function toggleMoreOptions() {
    const content = document.getElementById('moreOptionsContent');
    const chevron = document.getElementById('moreOptionsChevron');
    const label = document.getElementById('moreOptionsLabel');

    const isOpen = content.classList.toggle('is-open');

    if (chevron) {
        chevron.style.transform = isOpen ? 'rotate(90deg)' : 'rotate(0deg)';
    }
    if (label) {
        label.textContent = isOpen ? 'Fewer options' : 'More options';
    }
}

function resetMoreOptions() {
    const content = document.getElementById('moreOptionsContent');
    const chevron = document.getElementById('moreOptionsChevron');
    const label = document.getElementById('moreOptionsLabel');
    content.classList.remove('is-open');
    if (chevron) chevron.style.transform = 'rotate(0deg)';
    if (label) label.textContent = 'More options';
}

/* ================= OPEN CREATE ================= */
function openCreatePanel() {
    const form = document.getElementById('payeeForm');
    form.reset();
    form.action = "{{ route('backend.payees.store') }}";
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('payeeUuid').value = '';
    document.getElementById('payeePanelTitle').textContent = 'Add Payee';
    document.getElementById('payeePanelSubmit').textContent = 'Create Payee';

    // Reset dropdown to M-Pesa
    document.getElementById('paymentMethodSelect').value = 'mpesa';

    // Reset more options
    resetMoreOptions();

    // Reset payment fields
    onPaymentMethodChange();

    // Handle category
    if (presetCategory) {
        const lockedView = document.getElementById('categoryLockedView');
        const pickerView = document.getElementById('categoryPickerView');
        const hidden = document.getElementById('categoryHidden');
        if (lockedView) lockedView.style.display = 'block';
        if (pickerView) pickerView.style.display = 'none';
        if (hidden) hidden.value = presetCategory;
    } else {
        setTimeout(() => {
            const firstCat = document.querySelector('.cat-option[data-value="supplier"]');
            if (firstCat) selectCategory(firstCat);
            const radio = document.querySelector('input[name="category"][value="supplier"]');
            if (radio) radio.checked = true;
        }, 10);
    }

    openFormPanel();
}

/* ================= UNLOCK CATEGORY ================= */
function unlockCategory() {
    const locked = document.getElementById('categoryLockedView');
    const picker = document.getElementById('categoryPickerView');
    const hidden = document.getElementById('categoryHidden');

    if (locked) locked.style.display = 'none';
    if (picker) picker.style.display = 'block';
    if (hidden) hidden.remove();

    if (presetCategory) {
        const radio = document.querySelector(`input[name="category"][value="${presetCategory}"]`);
        if (radio) radio.checked = true;
        const catEl = document.querySelector(`.cat-option[data-value="${presetCategory}"]`);
        if (catEl) selectCategory(catEl);
    }
}

/* ================= OPEN EDIT ================= */
function openEditPanel(uuid) {
    const payee = allPayees[uuid];
    if (!payee) return;
    currentPayeeUuid = uuid;
    const form = document.getElementById('payeeForm');
    form.reset();
    form.action = "/backend/payees/" + uuid;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('payeeUuid').value = uuid;

    // Basic fields
    const fields = ['type','name','point_of_contact','phone','email','address','id_number','kra_pin','notes','status'];
    fields.forEach(f => {
        const el = form.elements[f];
        if (el && payee[f] != null) el.value = payee[f];
    });

    // Determine payment method based on which fields are filled
    let method = 'mpesa';
    if (payee.bank_name || payee.bank_account || payee.bank_branch) {
        method = 'bank';
    } else if (!payee.mpesa_number) {
        method = 'cash';
    }

    // Set the dropdown and fill fields
    document.getElementById('paymentMethodSelect').value = method;

    if (method === 'mpesa') {
        document.getElementById('mpesaInput').value = payee.mpesa_number || '';
    } else if (method === 'bank') {
        document.getElementById('bankNameInput').value = payee.bank_name || '';
        document.getElementById('bankAccountInput').value = payee.bank_account || '';
        document.getElementById('bankBranchInput').value = payee.bank_branch || '';
    }

    onPaymentMethodChange();

    // Category picker (always unlocked for edit)
    const lockedView = document.getElementById('categoryLockedView');
    const pickerView = document.getElementById('categoryPickerView');
    if (lockedView) lockedView.style.display = 'none';
    if (pickerView) pickerView.style.display = 'block';

    const radio = document.querySelector(`input[name="category"][value="${payee.category}"]`);
    if (radio) radio.checked = true;
    const catEl = document.querySelector(`.cat-option[data-value="${payee.category}"]`);
    if (catEl) selectCategory(catEl);

    // Open more options automatically if any "extra" field has data
    const hasExtra = payee.point_of_contact || payee.address || payee.id_number || payee.kra_pin || payee.notes;
    if (hasExtra) {
        const content = document.getElementById('moreOptionsContent');
        const chevron = document.getElementById('moreOptionsChevron');
        const label = document.getElementById('moreOptionsLabel');
        content.classList.add('is-open');
        if (chevron) chevron.style.transform = 'rotate(90deg)';
        if (label) label.textContent = 'Fewer options';
    } else {
        resetMoreOptions();
    }

    document.getElementById('payeePanelTitle').textContent = 'Edit Payee';
    document.getElementById('payeePanelSubmit').textContent = 'Save Changes';
    openFormPanel();
}

/* ================= SHOW MODAL ================= */
function showModal(id) { document.getElementById(id).style.display = 'block'; document.body.style.overflow = 'hidden'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; document.body.style.overflow = ''; }

function openShowModal(uuid) {
    const payee = allPayees[uuid];
    if (!payee) return;
    currentPayeeUuid = uuid;
    document.getElementById('showPayeeName').textContent = payee.name || 'Payee Details';

    const set = (id, val) => { document.getElementById(id).textContent = (val != null && val !== '') ? val : '—'; };
    set('show-payee-category', payee.category ? payee.category.charAt(0).toUpperCase() + payee.category.slice(1) : '');
    set('show-payee-type', payee.type);
    set('show-payee-poc', payee.point_of_contact);
    set('show-payee-phone', payee.phone);
    set('show-payee-email', payee.email);
    set('show-payee-mpesa', payee.mpesa_number);
    set('show-payee-bank', payee.bank_name);
    set('show-payee-account', payee.bank_account);
    set('show-payee-branch', payee.bank_branch);
    set('show-payee-idnum', payee.id_number);
    set('show-payee-kra', payee.kra_pin);
    set('show-payee-status', payee.status ? payee.status.charAt(0).toUpperCase() + payee.status.slice(1) : '');
    set('show-payee-address', payee.address);
    set('show-payee-notes', payee.notes);

    document.getElementById('showEditBtn').onclick = () => { closeModal('showPayeeModal'); openEditPanel(uuid); };
    showModal('showPayeeModal');
}

/* ================= DELETE ================= */
function deletePayee() {
    if (!currentPayeeUuid) return;
    if (!confirm('Delete this payee? This cannot be undone.')) return;
    const form = document.getElementById('deletePayeeForm');
    form.action = "/backend/payees/" + currentPayeeUuid;
    form.submit();
}

/* ================= GLOBAL ================= */
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeFormPanel();
        closeModal('showPayeeModal');
    }
});

document.addEventListener('DOMContentLoaded', () => {
    // Set initial category visual state
    const initialCat = document.querySelector('input[name="category"]:checked');
    if (initialCat) {
        const catEl = document.querySelector(`.cat-option[data-value="${initialCat.value}"]`);
        if (catEl) selectCategory(catEl);
    }

    // Initialize payment method state
    onPaymentMethodChange();
});

@if ($errors->any())
document.addEventListener('DOMContentLoaded', () => {
    const method = "{{ old('_method', 'POST') }}";
    if (method === 'PUT' && "{{ old('payee_uuid') }}") openEditPanel("{{ old('payee_uuid') }}");
    else openCreatePanel();
});
@endIf
</script>

@include('backend.includes.Footer')