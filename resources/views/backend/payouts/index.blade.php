@include('backend.includes.header')
@include('backend.includes.Sidebar')

<main class="app-main">
    <div style="padding: 28px 32px; width: 100%; box-sizing: border-box;">

        {{-- ============ HEADER ============ --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="font-size: 13px; color: #6b7280; margin-bottom: 6px;">
                    <a href="{{ route('dashboard') }}" style="color: #6b7280; text-decoration: none;">Dashboard</a>
                    <span> / </span>
                    <span>Finance</span>
                    <span> / </span>
                    <span style="color: #374151; font-weight: 500;">Payouts</span>
                </div>
                <h1 style="font-size: 26px; font-weight: 700; color: #111827; margin: 0;">Payouts</h1>
                <p style="font-size: 14px; color: #6b7280; margin: 6px 0 0 0;">Money sent to payees</p>
            </div>

            <button type="button" onclick="openCreatePanel()"
                    style="display: inline-flex; align-items: center; gap: 8px; padding: 11px 20px; background: #0ea5e9; color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                New Payout
            </button>
        </div>

        {{-- ============ FLASH ============ --}}
        @if(session('success'))
            <div style="padding: 14px 18px; background: #f0f9ff; border-left: 4px solid #0ea5e9; color: #0c4a6e; border-radius: 10px; margin-bottom: 20px; font-size: 14px;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div style="padding: 14px 18px; background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; border-radius: 10px; margin-bottom: 20px; font-size: 14px;">{{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div style="padding: 14px 18px; background: #eff6ff; border-left: 4px solid #3b82f6; color: #1e40af; border-radius: 10px; margin-bottom: 20px; font-size: 14px;">{{ session('info') }}</div>
        @endif

        {{-- ============ STATS ============ --}}
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 24px;">
            <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 14px; padding: 22px 24px;">
                <p style="font-size: 12px; font-weight: 700; color: #0369a1; text-transform: uppercase; letter-spacing: 0.06em; margin: 0;">Total Paid</p>
                <p style="font-size: 24px; font-weight: 700; color: #0c4a6e; margin: 10px 0 0 0; line-height: 1;">KES {{ number_format($totals['paid'], 0) }}</p>
            </div>
            <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 14px; padding: 22px 24px;">
                <p style="font-size: 12px; font-weight: 700; color: #92400e; text-transform: uppercase; letter-spacing: 0.06em; margin: 0;">Outstanding</p>
                <p style="font-size: 24px; font-weight: 700; color: #78350f; margin: 10px 0 0 0; line-height: 1;">KES {{ number_format($totals['pending'], 0) }}</p>
            </div>
            <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 22px 24px;">
                <p style="font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.06em; margin: 0;">Pending Approval</p>
                <p style="font-size: 24px; font-weight: 700; color: #111827; margin: 10px 0 0 0; line-height: 1;">{{ $counts['pending'] }}</p>
            </div>
            <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 22px 24px;">
                <p style="font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.06em; margin: 0;">Total Payouts</p>
                <p style="font-size: 24px; font-weight: 700; color: #111827; margin: 10px 0 0 0; line-height: 1;">{{ $counts['all'] }}</p>
            </div>
        </div>

        {{-- ============ FILTER TABS ============ --}}
        @php
            $currentStatus = request('status', 'all');
            $tabs = [
                'all'        => 'All',
                'pending'    => 'Pending',
                'approved'   => 'Approved',
                'processing' => 'Processing',
                'paid'       => 'Paid',
                'failed'     => 'Failed',
            ];
        @endphp
        <div style="display: flex; gap: 6px; margin-bottom: 18px; flex-wrap: wrap;">
            @foreach($tabs as $key => $label)
                <a href="{{ route('backend.payouts.index', ['status' => $key]) }}"
                   style="padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;
                          {{ $currentStatus === $key ? 'background: #0ea5e9; color: white;' : 'background: white; color: #6b7280; border: 1px solid #e5e7eb;' }}">
                    {{ $label }}
                    <span style="opacity: 0.7;">({{ $counts[$key] ?? 0 }})</span>
                </a>
            @endforeach
        </div>

        {{-- ============ SEARCH ============ --}}
        <form method="GET" style="background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 14px; margin-bottom: 18px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="status" value="{{ $currentStatus }}">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by payee name, transaction ref, or description..."
                   style="flex: 1; min-width: 220px; padding: 11px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; outline: none; background: #f9fafb;">
            <select name="payee_id" style="padding: 11px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; background: #f9fafb; outline: none; min-width: 180px;">
                <option value="">All Payees</option>
                @foreach($payees as $p)
                    <option value="{{ $p->id }}" @selected(request('payee_id') == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
            <button type="submit" style="padding: 11px 22px; background: #0ea5e9; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer;">Filter</button>
            @if(request()->hasAny(['search', 'payee_id']) || $currentStatus !== 'all')
                <a href="{{ route('backend.payouts.index') }}" style="padding: 11px 18px; background: #f3f4f6; color: #374151; border-radius: 10px; font-size: 14px; font-weight: 500; text-decoration: none;">Reset</a>
            @endif
        </form>

        {{-- ============ TABLE ============ --}}
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden;">
            @if($payouts->count())
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="background: #f0f9ff; border-bottom: 1px solid #bae6fd;">
                            <tr>
                                <th style="padding: 14px 20px; text-align: left; font-size: 12px; font-weight: 700; color: #075985; text-transform: uppercase; letter-spacing: 0.05em;">Payee</th>
                                <th style="padding: 14px 20px; text-align: left; font-size: 12px; font-weight: 700; color: #075985; text-transform: uppercase; letter-spacing: 0.05em;">Amount</th>
                                <th style="padding: 14px 20px; text-align: left; font-size: 12px; font-weight: 700; color: #075985; text-transform: uppercase; letter-spacing: 0.05em;">Method</th>
                                <th style="padding: 14px 20px; text-align: left; font-size: 12px; font-weight: 700; color: #075985; text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                                <th style="padding: 14px 20px; text-align: left; font-size: 12px; font-weight: 700; color: #075985; text-transform: uppercase; letter-spacing: 0.05em;">Date</th>
                                <th style="padding: 14px 20px; text-align: right; font-size: 12px; font-weight: 700; color: #075985; text-transform: uppercase; letter-spacing: 0.05em;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payouts as $payout)
                                @php
                                    $statusColors = [
                                        'pending'    => ['#fef3c7', '#92400e'],
                                        'approved'   => ['#dbeafe', '#1e40af'],
                                        'processing' => ['#ede9fe', '#5b21b6'],
                                        'paid'       => ['#d1fae5', '#065f46'],
                                        'failed'     => ['#fee2e2', '#991b1b'],
                                        'reversed'   => ['#f3f4f6', '#374151'],
                                    ];
                                    [$bg, $fg] = $statusColors[$payout->status] ?? ['#f3f4f6', '#374151'];
                                    $methodLabels = ['mpesa' => 'M-Pesa', 'bank' => 'Bank', 'cash' => 'Cash', 'cheque' => 'Cheque'];

                                    // ✅ Uses $isAdmin from controller — no DB query per row
                                    $canApprove = $payout->status === 'pending' && $isAdmin;
                                    $canPay     = in_array($payout->status, ['approved', 'processing']);
                                    $canDelete  = $payout->status !== 'paid';
                                @endphp
                                <tr style="border-bottom: 1px solid #f3f4f6;">
                                    <td style="padding: 14px 20px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #0ea5e9, #6366f1); color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0;">
                                                {{ strtoupper(substr($payout->payee->name ?? '?', 0, 1)) }}
                                            </div>
                                            <div style="min-width: 0;">
                                                <button type="button" onclick="openShowModal('{{ $payout->uuid }}')"
                                                        style="font-size: 14px; font-weight: 600; color: #111827; background: none; border: none; padding: 0; cursor: pointer; text-align: left;">
                                                    {{ $payout->payee->name ?? '—' }}
                                                </button>
                                                @if($payout->description)
                                                    <div style="font-size: 12px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 260px;">
                                                        {{ $payout->description }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 14px 20px;">
                                        <span style="font-size: 15px; font-weight: 700; color: #111827;">
                                            {{ $payout->currency }} {{ number_format($payout->amount, 0) }}
                                        </span>
                                    </td>
                                    <td style="padding: 14px 20px; color: #374151; font-size: 14px;">
                                        {{ $methodLabels[$payout->method] ?? $payout->method }}
                                    </td>
                                    <td style="padding: 14px 20px;">
                                        <span style="padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; background: {{ $bg }}; color: {{ $fg }};">
                                            {{ ucfirst($payout->status) }}
                                        </span>
                                    </td>
                                    <td style="padding: 14px 20px; font-size: 13px; color: #6b7280;">
                                        {{ $payout->created_at->format('d M Y') }}
                                    </td>
                                    <td style="padding: 14px 20px; text-align: right;">
                                        <div style="display: inline-flex; align-items: center; gap: 4px;">

                                            {{-- View --}}
                                            <button type="button" onclick="openShowModal('{{ $payout->uuid }}')"
                                                    title="View details"
                                                    style="padding: 7px; background: none; border: 1px solid transparent; border-radius: 8px; cursor: pointer; color: #6b7280;">
                                                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </button>

                                            {{-- Approve --}}
                                            @if($canApprove)
                                                <button type="button" onclick="approveFromRow('{{ $payout->uuid }}', event)"
                                                        title="Approve payout"
                                                        style="padding: 7px; background: none; border: 1px solid transparent; border-radius: 8px; cursor: pointer; color: #0ea5e9;">
                                                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                        <path d="M20 6 9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                            @endif

                                            {{-- Mark as Paid --}}
                                            @if($canPay)
                                                <button type="button" onclick="openMarkPaidFromRow('{{ $payout->uuid }}', event)"
                                                        title="Mark as paid"
                                                        style="padding: 7px; background: none; border: 1px solid transparent; border-radius: 8px; cursor: pointer; color: #10b981;">
                                                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                        <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-linecap="round"/>
                                                    </svg>
                                                </button>
                                            @endif

                                            {{-- Delete --}}
                                            @if($canDelete)
                                                <button type="button" onclick="deleteFromRow('{{ $payout->uuid }}', event)"
                                                        title="Delete payout"
                                                        style="padding: 7px; background: none; border: 1px solid transparent; border-radius: 8px; cursor: pointer; color: #dc2626;">
                                                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                            @endif

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($payouts->hasPages())
                    <div style="padding: 16px;">{{ $payouts->withQueryString()->links() }}</div>
                @endif
            @else
                <div style="padding: 64px 28px; text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 12px;">💸</div>
                    <h3 style="font-size: 16px; font-weight: 700; color: #374151; margin: 0 0 6px 0;">No payouts yet</h3>
                    <p style="font-size: 14px; color: #6b7280; margin: 0 0 24px 0;">Create your first payout to a supplier or personnel.</p>
                    <button type="button" onclick="openCreatePanel()"
                            style="display: inline-flex; align-items: center; gap: 8px; padding: 11px 20px; background: #0ea5e9; color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer;">
                        New Payout
                    </button>
                </div>
            @endif
        </div>
    </div>
</main>

{{-- ================================================================
     CREATE PAYOUT — RIGHT-SIDE DRAWER
     ================================================================ --}}
<div id="payoutFormPanel"
     style="display: none; position: fixed; top: 0; right: 0; bottom: 0; width: 640px; max-width: 100vw; background: #ffffff; box-shadow: -10px 0 30px rgba(0,0,0,0.15); z-index: 200; overflow-y: auto; transform: translateX(100%); transition: transform 0.3s ease;">

    <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px 28px; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; background: #ffffff; z-index: 10;">
        <div>
            <h3 style="font-size: 20px; font-weight: 700; color: #111827; margin: 0;">New Payout</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 4px 0 0 0;">Send money to a payee</p>
        </div>
        <button type="button" onclick="closeFormPanel()"
                style="padding: 8px; background: none; border: none; cursor: pointer; border-radius: 8px;">
            <svg style="width: 22px; height: 22px; color: #6b7280;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/>
            </svg>
        </button>
    </div>

    <form id="payoutForm" method="POST" action="{{ route('backend.payouts.store') }}">
        @csrf

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

            <div style="margin-bottom: 20px;">
                <label class="form-label">Payee <span style="color:#ef4444;">*</span></label>
                <select name="payee_id" id="payeeSelect" required class="form-input">
                    <option value="">— Select a payee —</option>
                    @foreach($payees as $p)
                        <option value="{{ $p->id }}"
                                data-mpesa="{{ $p->mpesa_number }}"
                                data-phone="{{ $p->phone }}"
                                @selected(old('payee_id') == $p->id)>
                            {{ $p->name }} ({{ ucfirst($p->category) }}){{ $p->type ? ' — ' . $p->type : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="form-label">Link to Service (optional)</label>
                <select name="service_id" class="form-input">
                    <option value="">— No service — (refund, advance, one-off)</option>
                    @foreach($services as $s)
                        <option value="{{ $s->id }}" @selected(old('service_id') == $s->id)>
                            {{ $s->event->name ?? '?' }} — {{ $s->description }} — KES {{ number_format($s->balance ?? 0, 0) }} remaining
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label class="form-label">Amount <span style="color:#ef4444;">*</span></label>
                    <input type="number" step="0.01" min="1" name="amount" value="{{ old('amount') }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Currency</label>
                    <select name="currency" class="form-input">
                        <option value="KES" @selected(old('currency', 'KES') === 'KES')>KES</option>
                        <option value="USD" @selected(old('currency') === 'USD')>USD</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="form-label">Payment Method <span style="color:#ef4444;">*</span></label>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                    @foreach(['mpesa' => 'M-Pesa', 'bank' => 'Bank', 'cash' => 'Cash', 'cheque' => 'Cheque'] as $val => $lbl)
                        <label style="cursor: pointer; position: relative;">
                            <input type="radio" name="method" value="{{ $val }}" style="position: absolute; opacity: 0; width: 0; height: 0;"
                                   onchange="onMethodChange()"
                                   @checked(old('method', 'mpesa') === $val)>
                            <div class="method-option" data-value="{{ $val }}"
                                 style="text-align: center; padding: 12px; border: 2px solid {{ old('method', 'mpesa') === $val ? '#0ea5e9' : '#e5e7eb' }}; border-radius: 10px; font-size: 14px; font-weight: 500; color: {{ old('method', 'mpesa') === $val ? '#ffffff' : '#6b7280' }}; background: {{ old('method', 'mpesa') === $val ? '#0ea5e9' : 'transparent' }}; cursor: pointer; transition: all 0.15s;">
                                {{ $lbl }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div id="phoneField" style="margin-bottom: 20px;">
                <label class="form-label">M-Pesa Phone Number</label>
                <input type="text" name="phone" id="phoneInput" value="{{ old('phone') }}" placeholder="254XXXXXXXXX" maxlength="20" class="form-input">
                <p style="font-size: 12px; color: #6b7280; margin: 6px 0 0 0;">Format: 254XXXXXXXXX</p>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="form-label">Description / Reason</label>
                <textarea name="description" rows="3" placeholder="e.g. Tent rental for What It Takes event" class="form-input">{{ old('description') }}</textarea>
            </div>

            <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 10px; padding: 14px 16px; margin-bottom: 8px;">
                <p style="font-size: 13px; color: #78350f; margin: 0;">
                    <strong>Note:</strong> Payouts under <strong>KES 50,000</strong> are auto-approved.
                    <strong>KES 50,000+</strong> require admin approval.
                </p>
            </div>

        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; padding: 20px 28px; border-top: 1px solid #e5e7eb; background: #f9fafb; position: sticky; bottom: 0;">
            <button type="button" onclick="closeFormPanel()"
                    style="padding: 11px 20px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-weight: 500; color: #374151; background: white; cursor: pointer;">Cancel</button>
            <button type="submit"
                    style="padding: 11px 24px; background: #0ea5e9; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">Create Payout</button>
        </div>
    </form>
</div>

<div id="payoutBackdrop" onclick="closeFormPanel()"
     style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.2); z-index: 199;"></div>

{{-- ================================================================
     SHOW PAYOUT — CENTERED MODAL
     ================================================================ --}}
<div id="showPayoutModal" style="display: none; position: fixed; inset: 0; z-index: 300; overflow-y: auto;">
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5);" onclick="closeModal('showPayoutModal')"></div>

    <div style="position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px;">
        <div style="position: relative; width: 100%; max-width: 640px; background: white; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">

            <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <div>
                    <h3 id="showPayoutTitle" style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Payout Details</h3>
                    <p id="showPayoutUuid" style="font-size: 12px; color: #9ca3af; margin: 4px 0 0 0; font-family: monospace;"></p>
                </div>
                <button type="button" onclick="closeModal('showPayoutModal')" style="padding: 6px; background: none; border: none; cursor: pointer; border-radius: 6px;">
                    <svg style="width: 20px; height: 20px; color: #6b7280;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            <div style="background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%); padding: 24px; color: white; text-align: center;">
                <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.9; margin: 0; font-weight: 600;">Payout Amount</p>
                <p id="showAmount" style="font-size: 36px; font-weight: 800; margin: 8px 0 0 0; line-height: 1;"></p>
                <p id="showStatusBadge" style="display: inline-block; margin-top: 12px; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(255,255,255,0.25);"></p>
            </div>

            <div style="padding: 24px; max-height: 55vh; overflow-y: auto;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px 24px; font-size: 14px;">
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Payee</span><span id="show-payee" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Method</span><span id="show-method" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Phone</span><span id="show-phone" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Service</span><span id="show-service" style="font-weight: 500; color: #111827;">—</span></div>
                    <div><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Created</span><span id="show-created" style="font-weight: 500; color: #111827;">—</span></div>
                    <div id="showApprovedBlock"><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Approved</span><span id="show-approved" style="font-weight: 500; color: #111827;">—</span></div>
                    <div id="showPaidBlock"><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Paid</span><span id="show-paid" style="font-weight: 500; color: #111827;">—</span></div>
                    <div id="showRefBlock"><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Transaction Ref</span><span id="show-ref" style="font-weight: 500; color: #111827; font-family: monospace;">—</span></div>
                    <div id="showDescBlock" style="grid-column: 1 / -1;"><span style="display:block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Description</span><span id="show-description" style="font-weight: 500; color: #111827; line-height: 1.5;">—</span></div>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-top: 1px solid #e5e7eb; background: #f9fafb; border-radius: 0 0 16px 16px; gap: 10px; flex-wrap: wrap;">
                <button type="button" id="showDeleteBtn" onclick="deletePayout()"
                        style="display: none; padding: 8px 14px; color: #dc2626; background: none; border: 1px solid #fecaca; cursor: pointer; border-radius: 8px; font-size: 14px; font-weight: 500;">
                    Delete
                </button>
                <div id="showActionSpacer" style="flex: 1;"></div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" onclick="closeModal('showPayoutModal')"
                            style="padding: 9px 18px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-weight: 500; color: #374151; background: white; cursor: pointer;">
                        Close
                    </button>

                    <button type="button" id="showApproveBtn" onclick="approvePayout()"
                            style="display: none; padding: 9px 18px; background: #0ea5e9; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                        Approve
                    </button>

                    <button type="button" id="showMarkPaidBtn" onclick="openMarkPaid()"
                            style="display: none; padding: 9px 18px; background: #10b981; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                        Mark as Paid
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ================================================================
     MARK AS PAID — SMALL MODAL
     ================================================================ --}}
<div id="markPaidModal" style="display: none; position: fixed; inset: 0; z-index: 400;">
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.6);" onclick="closeMarkPaid()"></div>

    <div style="position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px;">
        <div style="width: 100%; max-width: 440px; background: white; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
            <form id="markPaidForm" method="POST">
                @csrf
                <div style="padding: 24px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: #111827; margin: 0 0 6px 0;">Mark as Paid</h3>
                    <p id="markPaidText" style="font-size: 14px; color: #6b7280; margin: 0 0 20px 0;"></p>

                    <label class="form-label">Transaction Reference (optional)</label>
                    <input type="text" name="transaction_ref" placeholder="M-Pesa receipt, bank ref, cheque no." class="form-input">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 8px; padding: 16px 24px; border-top: 1px solid #e5e7eb; background: #f9fafb; border-radius: 0 0 16px 16px;">
                    <button type="button" onclick="closeMarkPaid()"
                            style="padding: 10px 18px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-weight: 500; color: #374151; background: white; cursor: pointer;">Cancel</button>
                    <button type="submit"
                            style="padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">Confirm Paid</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Hidden action forms --}}
<form id="deletePayoutForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<form id="approvePayoutForm" method="POST" style="display: none;">
    @csrf
</form>

<style>
    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }
    .form-input {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        outline: none;
        box-sizing: border-box;
        background: white;
    }
    .form-input:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 3px rgba(14,165,233,0.1);
    }
</style>

<script>
const allPayouts = @json(collect($payouts->items())->keyBy('uuid'));
const isAdmin = @json($isAdmin);
let currentPayoutUuid = null;

/* ================= DRAWER — CREATE ================= */
function openCreatePanel() {
    const form = document.getElementById('payoutForm');
    form.reset();
    form.action = "{{ route('backend.payouts.store') }}";
    onMethodChange();
    openFormPanel();
}

function openFormPanel() {
    const panel = document.getElementById('payoutFormPanel');
    const backdrop = document.getElementById('payoutBackdrop');
    panel.style.display = 'block';
    backdrop.style.display = 'block';
    requestAnimationFrame(() => { panel.style.transform = 'translateX(0)'; });
    document.body.style.overflow = 'hidden';
}

function closeFormPanel() {
    const panel = document.getElementById('payoutFormPanel');
    const backdrop = document.getElementById('payoutBackdrop');
    panel.style.transform = 'translateX(100%)';
    setTimeout(() => {
        panel.style.display = 'none';
        backdrop.style.display = 'none';
    }, 300);
    document.body.style.overflow = '';
}

/* ================= METHOD SWITCH ================= */
function onMethodChange() {
    const methodEl = document.querySelector('input[name="method"]:checked');
    if (!methodEl) return;
    const method = methodEl.value;

    document.querySelectorAll('.method-option').forEach((el) => {
        if (el.dataset.value === method) {
            el.style.background = '#0ea5e9';
            el.style.color = '#ffffff';
            el.style.borderColor = '#0ea5e9';
        } else {
            el.style.background = 'transparent';
            el.style.color = '#6b7280';
            el.style.borderColor = '#e5e7eb';
        }
    });

    document.getElementById('phoneField').style.display = (method === 'mpesa') ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('payeeSelect');
    if (sel) {
        sel.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const phoneInput = document.getElementById('phoneInput');
            if (!phoneInput.value) {
                phoneInput.value = opt.dataset.mpesa || opt.dataset.phone || '';
            }
        });
    }
    onMethodChange();
});

/* ================= SHOW MODAL ================= */
function showModal(id) { document.getElementById(id).style.display = 'block'; document.body.style.overflow = 'hidden'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; document.body.style.overflow = ''; }

function openShowModal(uuid) {
    const payout = allPayouts[uuid];
    if (!payout) return;
    currentPayoutUuid = uuid;

    document.getElementById('showPayoutTitle').textContent = 'Payout to ' + (payout.payee?.name || '—');
    document.getElementById('showPayoutUuid').textContent = uuid;
    document.getElementById('showAmount').textContent = (payout.currency || 'KES') + ' ' + Number(payout.amount).toLocaleString();

    const set = (id, val) => { document.getElementById(id).textContent = (val !== null && val !== undefined && val !== '') ? val : '—'; };
    set('show-payee', payout.payee?.name);
    set('show-method', payout.method ? payout.method.charAt(0).toUpperCase() + payout.method.slice(1) : '');
    set('show-phone', payout.phone);
    set('show-service', payout.service?.description);
    set('show-created', formatDateTime(payout.created_at));
    set('show-approved', payout.approved_at ? formatDateTime(payout.approved_at) + (payout.approver ? ' — ' + payout.approver.name : '') : null);
    set('show-paid', payout.paid_at ? formatDateTime(payout.paid_at) + (payout.payer ? ' — ' + payout.payer.name : '') : null);
    set('show-ref', payout.transaction_ref);
    set('show-description', payout.description);

    document.getElementById('showApprovedBlock').style.display = payout.approved_at ? 'block' : 'none';
    document.getElementById('showPaidBlock').style.display = payout.paid_at ? 'block' : 'none';
    document.getElementById('showRefBlock').style.display = payout.transaction_ref ? 'block' : 'none';
    document.getElementById('showDescBlock').style.display = payout.description ? 'block' : 'none';

    const badge = document.getElementById('showStatusBadge');
    badge.textContent = payout.status.charAt(0).toUpperCase() + payout.status.slice(1);

    const approveBtn = document.getElementById('showApproveBtn');
    const markPaidBtn = document.getElementById('showMarkPaidBtn');
    const deleteBtn = document.getElementById('showDeleteBtn');

    approveBtn.style.display = (payout.status === 'pending' && isAdmin) ? 'inline-block' : 'none';
    markPaidBtn.style.display = (payout.status === 'approved' || payout.status === 'processing') ? 'inline-block' : 'none';
    deleteBtn.style.display = (payout.status !== 'paid') ? 'inline-block' : 'none';

    showModal('showPayoutModal');
}

function formatDateTime(dt) {
    if (!dt) return null;
    const d = new Date(dt);
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) +
           ', ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
}

/* ================= ROW ACTIONS ================= */
function approveFromRow(uuid, event) {
    event.stopPropagation();
    if (!confirm('Approve this payout?')) return;
    const form = document.getElementById('approvePayoutForm');
    form.action = "/backend/payouts/" + uuid + "/approve";
    form.submit();
}

function openMarkPaidFromRow(uuid, event) {
    event.stopPropagation();
    currentPayoutUuid = uuid;
    openMarkPaid();
}

function deleteFromRow(uuid, event) {
    event.stopPropagation();
    if (!confirm('Delete this payout? This cannot be undone.')) return;
    const form = document.getElementById('deletePayoutForm');
    form.action = "/backend/payouts/" + uuid;
    form.submit();
}

/* ================= MODAL ACTIONS ================= */
function approvePayout() {
    if (!currentPayoutUuid) return;
    if (!confirm('Approve this payout?')) return;
    const form = document.getElementById('approvePayoutForm');
    form.action = "/backend/payouts/" + currentPayoutUuid + "/approve";
    form.submit();
}

function deletePayout() {
    if (!currentPayoutUuid) return;
    if (!confirm('Delete this payout? This cannot be undone.')) return;
    const form = document.getElementById('deletePayoutForm');
    form.action = "/backend/payouts/" + currentPayoutUuid;
    form.submit();
}

/* ================= MARK PAID ================= */
function openMarkPaid() {
    const payout = allPayouts[currentPayoutUuid];
    if (!payout) return;
    document.getElementById('markPaidText').textContent =
        'Record that ' + (payout.currency || 'KES') + ' ' + Number(payout.amount).toLocaleString() +
        ' was paid to ' + (payout.payee?.name || '—') + '.';
    const form = document.getElementById('markPaidForm');
    form.action = "/backend/payouts/" + currentPayoutUuid + "/mark-paid";
    document.getElementById('markPaidModal').style.display = 'block';
}

function closeMarkPaid() {
    document.getElementById('markPaidModal').style.display = 'none';
}

/* ================= ESC ================= */
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeFormPanel();
        closeModal('showPayoutModal');
        closeMarkPaid();
    }
});

@if ($errors->any())
document.addEventListener('DOMContentLoaded', () => { openCreatePanel(); });
@endif
</script>

@include('backend.includes.Footer')