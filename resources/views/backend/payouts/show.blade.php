@include('backend.includes.header')
@include('backend.includes.Sidebar')

<main class="app-main">
    <div style="padding: 28px 32px; width: 100%; box-sizing: border-box; max-width: 900px;">

        <div style="margin-bottom: 24px;">
            <a href="{{ route('backend.payouts.index') }}" style="font-size: 13px; color: #6b7280; text-decoration: none;">← Back to Payouts</a>
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 10px; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h1 style="font-size: 26px; font-weight: 700; color: #111827; margin: 0;">Payout Details</h1>
                    <p style="font-size: 13px; color: #6b7280; margin: 4px 0 0 0;">{{ $payout->uuid }}</p>
                </div>

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
                @endphp
                <span style="padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; background: {{ $bg }}; color: {{ $fg }}; text-transform: uppercase; letter-spacing: 0.05em;">
                    {{ $payout->status }}
                </span>
            </div>
        </div>

        @if(session('success'))
            <div style="padding: 14px 18px; background: #f0f9ff; border-left: 4px solid #0ea5e9; color: #0c4a6e; border-radius: 10px; margin-bottom: 20px; font-size: 14px;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="padding: 14px 18px; background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; border-radius: 10px; margin-bottom: 20px; font-size: 14px;">
                {{ session('error') }}
            </div>
        @endif

        {{-- ============ AMOUNT CARD ============ --}}
        <div style="background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%); border-radius: 16px; padding: 28px 32px; color: white; margin-bottom: 24px;">
            <p style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.9; margin: 0; font-weight: 600;">Payout Amount</p>
            <p style="font-size: 42px; font-weight: 800; margin: 8px 0 0 0; line-height: 1;">
                {{ $payout->currency }} {{ number_format($payout->amount, 2) }}
            </p>
            <p style="font-size: 14px; margin: 12px 0 0 0; opacity: 0.9;">
                To <strong>{{ $payout->payee->name ?? '—' }}</strong>
                via <strong>{{ ucfirst($payout->method) }}</strong>
            </p>
        </div>

        {{-- ============ ACTION BUTTONS ============ --}}
        <div style="display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap;">

            @if($payout->status === 'pending')
                @if(auth()->user()->hasRole('admin'))
                    <form method="POST" action="{{ route('backend.payouts.approve', $payout) }}">
                        @csrf
                        <button type="submit" style="padding: 11px 20px; background: #0ea5e9; color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer;">
                            ✓ Approve Payout
                        </button>
                    </form>
                @else
                    <div style="padding: 12px 20px; background: #fef3c7; border-radius: 10px; font-size: 14px; color: #78350f;">
                        Awaiting admin approval (amount ≥ KES 50,000)
                    </div>
                @endif
            @endif

            @if(in_array($payout->status, ['approved', 'processing']))
                <button type="button" onclick="openMarkPaid()"
                        style="padding: 11px 20px; background: #10b981; color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer;">
                    Mark as Paid
                </button>

                @if($payout->method === 'mpesa')
                    <button type="button" onclick="alert('M-Pesa B2C integration coming soon. For now, pay manually then mark as paid.')"
                            style="padding: 11px 20px; background: white; color: #0ea5e9; border: 2px solid #0ea5e9; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer;">
                        Send via M-Pesa (B2C)
                    </button>
                @endif
            @endif

            @if($payout->status !== 'paid')
                <form method="POST" action="{{ route('backend.payouts.destroy', $payout) }}" onsubmit="return confirm('Delete this payout?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="padding: 11px 20px; background: white; color: #dc2626; border: 1px solid #fecaca; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer;">
                        Delete
                    </button>
                </form>
            @endif
        </div>

        {{-- ============ DETAILS GRID ============ --}}
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 24px; margin-bottom: 20px;">
            <h2 style="font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 18px 0; text-transform: uppercase; letter-spacing: 0.05em;">Details</h2>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px 32px; font-size: 14px;">

                <div>
                    <span style="display: block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Payee</span>
                    <span style="color: #111827; font-weight: 600;">{{ $payout->payee->name ?? '—' }}</span>
                    @if($payout->payee)
                        <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">{{ ucfirst($payout->payee->category) }}{{ $payout->payee->type ? ' — ' . $payout->payee->type : '' }}</div>
                    @endif
                </div>

                <div>
                    <span style="display: block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Method</span>
                    <span style="color: #111827; font-weight: 600;">{{ ucfirst($payout->method) }}</span>
                </div>

                <div>
                    <span style="display: block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Phone (M-Pesa)</span>
                    <span style="color: #111827; font-weight: 500;">{{ $payout->phone ?? '—' }}</span>
                </div>

                <div>
                    <span style="display: block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Service</span>
                    <span style="color: #111827; font-weight: 500;">
                        @if($payout->service)
                            {{ $payout->service->description }}
                            @if($payout->service->event)
                                <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">{{ $payout->service->event->name }}</div>
                            @endif
                        @else
                            —
                        @endif
                    </span>
                </div>

                <div>
                    <span style="display: block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Created</span>
                    <span style="color: #111827;">{{ $payout->created_at->format('d M Y, H:i') }}</span>
                </div>

                @if($payout->approved_at)
                    <div>
                        <span style="display: block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Approved</span>
                        <span style="color: #111827;">{{ $payout->approved_at->format('d M Y, H:i') }}</span>
                        @if($payout->approver)
                            <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">by {{ $payout->approver->name }}</div>
                        @endif
                    </div>
                @endif

                @if($payout->paid_at)
                    <div>
                        <span style="display: block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Paid</span>
                        <span style="color: #111827;">{{ $payout->paid_at->format('d M Y, H:i') }}</span>
                        @if($payout->payer)
                            <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">by {{ $payout->payer->name }}</div>
                        @endif
                    </div>
                @endif

                @if($payout->transaction_ref)
                    <div>
                        <span style="display: block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Transaction Ref</span>
                        <span style="color: #111827; font-family: monospace;">{{ $payout->transaction_ref }}</span>
                    </div>
                @endif

                @if($payout->description)
                    <div style="grid-column: 1 / -1;">
                        <span style="display: block; font-size: 10px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Description</span>
                        <span style="color: #111827; line-height: 1.5;">{{ $payout->description }}</span>
                    </div>
                @endif

            </div>
        </div>

    </div>
</main>

{{-- ============ MARK PAID MODAL ============ --}}
<div id="markPaidModal" style="display: none; position: fixed; inset: 0; z-index: 300;">
    <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5);" onclick="closeMarkPaid()"></div>
    <div style="position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px;">
        <div style="width: 100%; max-width: 460px; background: white; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
            <form method="POST" action="{{ route('backend.payouts.mark-paid', $payout) }}">
                @csrf
                <div style="padding: 24px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: #111827; margin: 0 0 6px 0;">Mark as Paid</h3>
                    <p style="font-size: 14px; color: #6b7280; margin: 0 0 20px 0;">
                        Record that {{ $payout->currency }} {{ number_format($payout->amount, 2) }} was paid to {{ $payout->payee->name ?? '—' }}.
                    </p>

                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">Transaction Reference (optional)</label>
                    <input type="text" name="transaction_ref" placeholder="M-Pesa receipt, bank ref, cheque no."
                           style="width: 100%; padding: 11px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 8px; padding: 16px 24px; border-top: 1px solid #e5e7eb; background: #f9fafb; border-radius: 0 0 16px 16px;">
                    <button type="button" onclick="closeMarkPaid()"
                            style="padding: 10px 18px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-weight: 500; color: #374151; background: white; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit"
                            style="padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                        Confirm Paid
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openMarkPaid() {
    document.getElementById('markPaidModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}
function closeMarkPaid() {
    document.getElementById('markPaidModal').style.display = 'none';
    document.body.style.overflow = '';
}
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMarkPaid();
});
</script>

@include('backend.includes.Footer')