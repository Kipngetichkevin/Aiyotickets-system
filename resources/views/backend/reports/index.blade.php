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
                    <span style="color: #374151; font-weight: 500;">Reports</span>
                </div>
                <h1 style="font-size: 26px; font-weight: 700; color: #111827; margin: 0;">Reports</h1>
                <p style="font-size: 14px; color: #6b7280; margin: 6px 0 0 0;">
                    Revenue, payouts, and profit per event
                </p>
            </div>

            <div style="display: flex; gap: 8px;">
                <a href="{{ route('backend.reports.export.csv', request()->query()) }}"
                   style="display: inline-flex; align-items: center; gap: 8px; padding: 11px 20px; background: white; color: #374151; border: 1px solid #e5e7eb; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Export CSV
                </a>
            </div>
        </div>

        {{-- ============ FILTERS ============ --}}
        <form method="GET" action="{{ route('backend.reports') }}"
              style="background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 16px 18px; margin-bottom: 22px; display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">

            <div style="flex: 1; min-width: 160px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #6b7280; margin-bottom: 4px;">From</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       style="width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; outline: none; background: #f9fafb;">
            </div>

            <div style="flex: 1; min-width: 160px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #6b7280; margin-bottom: 4px;">To</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       style="width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; outline: none; background: #f9fafb;">
            </div>

            <div style="flex: 2; min-width: 220px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #6b7280; margin-bottom: 4px;">Event</label>
                <select name="event_id"
                        style="width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; outline: none; background: #f9fafb;">
                    <option value="">All events</option>
                    @foreach($allEvents as $ev)
                        <option value="{{ $ev->id }}" @selected(request('event_id') == $ev->id)>
                            {{ $ev->name }} — {{ \Carbon\Carbon::parse($ev->date)->format('d M Y') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                    style="padding: 10px 24px; background: #0ea5e9; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer;">
                Apply
            </button>

            @if(request()->hasAny(['from', 'to', 'event_id']))
                <a href="{{ route('backend.reports') }}"
                   style="padding: 10px 18px; background: #f3f4f6; color: #374151; border-radius: 10px; font-size: 14px; font-weight: 500; text-decoration: none;">
                    Reset
                </a>
            @endif
        </form>

        {{-- ============ STAT CARDS ============ --}}
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 24px;">

            <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 14px; padding: 22px 24px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <div style="width: 36px; height: 36px; background: #0ea5e9; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                            <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span style="font-size: 12px; font-weight: 700; color: #0369a1; text-transform: uppercase; letter-spacing: 0.06em;">Revenue</span>
                </div>
                <p style="font-size: 26px; font-weight: 800; color: #0c4a6e; margin: 0; line-height: 1;">
                    KES {{ number_format($totalRevenue, 0) }}
                </p>
                <p style="font-size: 12px; color: #0369a1; margin: 6px 0 0 0;">{{ $totalTickets }} tickets sold</p>
            </div>

            <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 14px; padding: 22px 24px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <div style="width: 36px; height: 36px; background: #f59e0b; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                            <path d="M12 19V5M5 12l7 7 7-7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span style="font-size: 12px; font-weight: 700; color: #92400e; text-transform: uppercase; letter-spacing: 0.06em;">Payouts</span>
                </div>
                <p style="font-size: 26px; font-weight: 800; color: #78350f; margin: 0; line-height: 1;">
                    KES {{ number_format($totalPayouts, 0) }}
                </p>
                <p style="font-size: 12px; color: #92400e; margin: 6px 0 0 0;">to suppliers & personnel</p>
            </div>

            <div style="background: {{ $totalProfit >= 0 ? '#ecfdf5' : '#fef2f2' }}; border: 1px solid {{ $totalProfit >= 0 ? '#a7f3d0' : '#fecaca' }}; border-radius: 14px; padding: 22px 24px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <div style="width: 36px; height: 36px; background: {{ $totalProfit >= 0 ? '#10b981' : '#ef4444' }}; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" stroke-linecap="round" stroke-linejoin="round"/>
                            <polyline points="16 7 22 7 22 13" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span style="font-size: 12px; font-weight: 700; color: {{ $totalProfit >= 0 ? '#065f46' : '#991b1b' }}; text-transform: uppercase; letter-spacing: 0.06em;">Net Profit</span>
                </div>
                <p style="font-size: 26px; font-weight: 800; color: {{ $totalProfit >= 0 ? '#064e3b' : '#7f1d1d' }}; margin: 0; line-height: 1;">
                    KES {{ number_format($totalProfit, 0) }}
                </p>
                <p style="font-size: 12px; color: {{ $totalProfit >= 0 ? '#065f46' : '#991b1b' }}; margin: 6px 0 0 0;">
                    {{ number_format($totalMargin, 1) }}% margin
                </p>
            </div>

            <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 22px 24px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <div style="width: 36px; height: 36px; background: #6366f1; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span style="font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.06em;">Events</span>
                </div>
                <p style="font-size: 26px; font-weight: 800; color: #111827; margin: 0; line-height: 1;">
                    {{ $totalEvents }}
                </p>
                <p style="font-size: 12px; color: #6b7280; margin: 6px 0 0 0;">in this report</p>
            </div>

        </div>

        {{-- ============ PER-EVENT TABLE ============ --}}
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; margin-bottom: 24px;">
            <div style="padding: 18px 22px; border-bottom: 1px solid #f1f5f9;">
                <h2 style="font-size: 15px; font-weight: 700; color: #111827; margin: 0;">Breakdown by Event</h2>
                <p style="font-size: 13px; color: #6b7280; margin: 4px 0 0 0;">
                    Revenue and payouts per event
                </p>
            </div>

            @if(count($rows))
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="background: #f0f9ff; border-bottom: 1px solid #bae6fd;">
                            <tr>
                                <th style="padding: 14px 20px; text-align: left; font-size: 12px; font-weight: 700; color: #075985; text-transform: uppercase; letter-spacing: 0.05em;">Event</th>
                                <th style="padding: 14px 20px; text-align: left; font-size: 12px; font-weight: 700; color: #075985; text-transform: uppercase; letter-spacing: 0.05em;">Date</th>
                                <th style="padding: 14px 20px; text-align: right; font-size: 12px; font-weight: 700; color: #075985; text-transform: uppercase; letter-spacing: 0.05em;">Tickets</th>
                                <th style="padding: 14px 20px; text-align: right; font-size: 12px; font-weight: 700; color: #075985; text-transform: uppercase; letter-spacing: 0.05em;">Revenue</th>
                                <th style="padding: 14px 20px; text-align: right; font-size: 12px; font-weight: 700; color: #075985; text-transform: uppercase; letter-spacing: 0.05em;">Payouts</th>
                                <th style="padding: 14px 20px; text-align: right; font-size: 12px; font-weight: 700; color: #075985; text-transform: uppercase; letter-spacing: 0.05em;">Profit</th>
                                <th style="padding: 14px 20px; text-align: right; font-size: 12px; font-weight: 700; color: #075985; text-transform: uppercase; letter-spacing: 0.05em;">Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                @php
                                    $isProfitable = $row['profit'] >= 0;
                                @endphp
                                <tr style="border-bottom: 1px solid #f3f4f6;">
                                    <td style="padding: 14px 20px;">
                                        <div style="font-size: 14px; font-weight: 600; color: #111827;">
                                            {{ $row['event']->name }}
                                        </div>
                                        <div style="font-size: 12px; color: #6b7280;">
                                            {{ $row['event']->location ?? '—' }}
                                        </div>
                                    </td>
                                    <td style="padding: 14px 20px; font-size: 13px; color: #374151;">
                                        {{ \Carbon\Carbon::parse($row['event']->date)->format('d M Y') }}
                                    </td>
                                    <td style="padding: 14px 20px; text-align: right; font-size: 14px; color: #111827; font-weight: 500;">
                                        {{ number_format($row['tickets_sold']) }}
                                    </td>
                                    <td style="padding: 14px 20px; text-align: right; font-size: 14px; color: #0c4a6e; font-weight: 600;">
                                        KES {{ number_format($row['revenue'], 0) }}
                                    </td>
                                    <td style="padding: 14px 20px; text-align: right; font-size: 14px; color: #92400e; font-weight: 600;">
                                        KES {{ number_format($row['payouts'], 0) }}
                                    </td>
                                    <td style="padding: 14px 20px; text-align: right; font-size: 14px; font-weight: 700; color: {{ $isProfitable ? '#065f46' : '#991b1b' }};">
                                        KES {{ number_format($row['profit'], 0) }}
                                    </td>
                                    <td style="padding: 14px 20px; text-align: right;">
                                        <span style="display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700;
                                            background: {{ $isProfitable ? '#d1fae5' : '#fee2e2' }};
                                            color: {{ $isProfitable ? '#065f46' : '#991b1b' }};">
                                            {{ number_format($row['margin'], 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="background: #f9fafb; border-top: 2px solid #e5e7eb;">
                            <tr>
                                <td colspan="2" style="padding: 14px 20px; font-size: 13px; font-weight: 700; color: #374151;">
                                    Totals ({{ $totalEvents }} {{ \Illuminate\Support\Str::plural('event', $totalEvents) }})
                                </td>
                                <td style="padding: 14px 20px; text-align: right; font-size: 14px; font-weight: 700; color: #111827;">
                                    {{ number_format($totalTickets) }}
                                </td>
                                <td style="padding: 14px 20px; text-align: right; font-size: 14px; font-weight: 700; color: #0c4a6e;">
                                    KES {{ number_format($totalRevenue, 0) }}
                                </td>
                                <td style="padding: 14px 20px; text-align: right; font-size: 14px; font-weight: 700; color: #92400e;">
                                    KES {{ number_format($totalPayouts, 0) }}
                                </td>
                                <td style="padding: 14px 20px; text-align: right; font-size: 14px; font-weight: 800; color: {{ $totalProfit >= 0 ? '#065f46' : '#991b1b' }};">
                                    KES {{ number_format($totalProfit, 0) }}
                                </td>
                                <td style="padding: 14px 20px; text-align: right; font-size: 13px; font-weight: 700; color: {{ $totalProfit >= 0 ? '#065f46' : '#991b1b' }};">
                                    {{ number_format($totalMargin, 1) }}%
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div style="padding: 60px 28px; text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 12px;">📊</div>
                    <h3 style="font-size: 16px; font-weight: 700; color: #374151; margin: 0 0 6px 0;">
                        @if(request()->hasAny(['from', 'to', 'event_id']))
                            No data for this filter
                        @else
                            No reports to show yet
                        @endif
                    </h3>
                    <p style="font-size: 14px; color: #6b7280; margin: 0;">
                        @if(request()->hasAny(['from', 'to', 'event_id']))
                            Try adjusting your date range or <a href="{{ route('backend.reports') }}" style="color:#0284c7;">reset filters</a>.
                        @else
                            Create an event and sell tickets to see reports.
                        @endif
                    </p>
                </div>
            @endif
        </div>

    </div>
</main>

@include('backend.includes.Footer')