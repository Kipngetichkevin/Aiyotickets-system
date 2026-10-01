<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>M-Pesa Callbacks Report</title>
    <style>
        @page { margin: 28px 32px; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            margin: 0;
        }
        .header {
            border-bottom: 2px solid {{ $primaryColor }};
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: middle; }
        .logo { max-height: 44px; }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: {{ $primaryColor }};
            margin: 0;
        }
        .subtitle {
            font-size: 10px;
            color: #6b7280;
            margin: 2px 0 0;
        }
        .meta {
            text-align: right;
            font-size: 9px;
            color: #6b7280;
            line-height: 1.5;
        }

        .summary { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .summary td {
            width: 25%;
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        .summary .label { font-size: 8px; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px; }
        .summary .value { font-size: 16px; font-weight: bold; margin-top: 2px; }

        table.data { width: 100%; border-collapse: collapse; }
        table.data thead th {
            background: #f3f4f6;
            color: #374151;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            text-align: left;
            padding: 7px 6px;
            border-bottom: 1px solid #d1d5db;
        }
        table.data tbody td {
            padding: 6px;
            border-bottom: 1px solid #eef0f3;
            font-size: 9px;
            color: #374151;
            vertical-align: top;
        }
        table.data tbody tr:nth-child(even) td { background: #fafafa; }
        .mono { font-family: DejaVu Sans Mono, monospace; font-size: 8.5px; }

        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-transform: capitalize;
        }
        .badge-processed { background: #d1fae5; color: #047857; }
        .badge-failed    { background: #fee2e2; color: #b91c1c; }
        .badge-received  { background: #fef3c7; color: #b45309; }

        .result-ok   { color: #059669; font-weight: bold; }
        .result-fail { color: #dc2626; font-weight: bold; }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 8px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <table>
            <tr>
                <td>
                    @if($logoData)
                        <img src="{{ $logoData }}" class="logo" alt="Logo">
                    @else
                        <div class="title">{{ $appName }}</div>
                    @endif
                    <p class="title" style="margin-top:6px;">M-Pesa Callbacks Report</p>
                    <p class="subtitle">Transaction callbacks and processing status</p>
                </td>
                <td class="meta">
                    <div><strong>Generated:</strong> {{ $generatedAt }}</div>
                    <div><strong>Total rows:</strong> {{ $callbacks->count() }}</div>
                    @if(request('status'))
                        <div><strong>Status filter:</strong> {{ ucfirst(request('status')) }}</div>
                    @endif
                    @if(request('phone'))
                        <div><strong>Phone filter:</strong> {{ request('phone') }}</div>
                    @endif
                    @if(request('q'))
                        <div><strong>Search:</strong> {{ request('q') }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- SUMMARY --}}
    <table class="summary">
        <tr>
            <td>
                <div class="label">All</div>
                <div class="value" style="color:#1f2937;">{{ $counts['all'] }}</div>
            </td>
            <td>
                <div class="label">Received</div>
                <div class="value" style="color:#b45309;">{{ $counts['received'] }}</div>
            </td>
            <td>
                <div class="label">Processed</div>
                <div class="value" style="color:#047857;">{{ $counts['processed'] }}</div>
            </td>
            <td>
                <div class="label">Failed</div>
                <div class="value" style="color:#b91c1c;">{{ $counts['failed'] }}</div>
            </td>
        </tr>
    </table>

    {{-- TABLE --}}
    <table class="data">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:14%;">Received</th>
                <th style="width:12%;">Phone</th>
                <th style="width:11%;">Amount</th>
                <th style="width:7%;">Result</th>
                <th style="width:13%;">Receipt</th>
                <th style="width:15%;">Order</th>
                <th style="width:12%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($callbacks as $cb)
                <tr>
                    <td class="mono">#{{ $cb->id }}</td>
                    <td>{{ $cb->created_at->format('d M Y H:i:s') }}</td>
                    <td class="mono">{{ $cb->phone_number ?? '—' }}</td>
                    <td>
                        @if($cb->amount !== null)
                            KES {{ number_format($cb->amount, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="mono">
                        @if($cb->result_code === '0')
                            <span class="result-ok">{{ $cb->result_code }}</span>
                        @elseif($cb->result_code)
                            <span class="result-fail">{{ $cb->result_code }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="mono">{{ $cb->mpesa_receipt_number ?? '—' }}</td>
                    <td class="mono">
                        {{ $cb->order->order_no ?? '—' }}
                    </td>
                    <td>
                        <span class="badge badge-{{ $cb->processing_status }}">
                            {{ ucfirst($cb->processing_status) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:20px; color:#9ca3af;">
                        No callbacks match the current filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        {{ $appName }} — Generated automatically on {{ $generatedAt }}
    </div>

</body>
</html>