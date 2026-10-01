<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket #{{ $attendee->id }}</title>
    <style>
        @page { margin: 30px 34px; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
        }
        .header { border-bottom: 2px solid {{ $primaryColor }}; padding-bottom: 12px; margin-bottom: 22px; }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: middle; }
        .logo { max-height: 46px; }
        .brand { font-size: 18px; font-weight: bold; color: {{ $primaryColor }}; margin: 0; }
        .brand-sub { font-size: 10px; color: #6b7280; margin-top: 2px; }
        .meta { text-align: right; font-size: 9px; color: #6b7280; line-height: 1.6; }

        .event-title { font-size: 20px; font-weight: bold; color: #111827; margin: 0 0 4px; }
        .event-sub { font-size: 10px; color: #6b7280; margin: 0 0 20px; }

        .ticket-box {
            border: 2px dashed {{ $primaryColor }};
            border-radius: 10px;
            padding: 20px 22px;
            margin-bottom: 22px;
        }
        .ticket-box table { width: 100%; border-collapse: collapse; }
        .ticket-box td { vertical-align: top; padding: 6px 0; }
        .label { font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.6px; color: #9ca3af; }
        .value { font-size: 13px; font-weight: bold; color: #111827; margin-top: 3px; }
        .value.mono { font-family: DejaVu Sans Mono, monospace; font-size: 12px; }

        table.details { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.details th,
        table.details td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            font-size: 10px;
        }
        table.details th {
            background: #f3f4f6;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #374151;
        }

        .footer {
            margin-top: 26px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            font-size: 8.5px;
            color: #9ca3af;
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            background: {{ $primaryColor }}22;
            color: {{ $primaryColor }};
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
                        <p class="brand">{{ $appName }}</p>
                    @endif
                    <p class="brand-sub">Official Event Ticket</p>
                </td>
                <td class="meta">
                    <div><strong>Ticket ID:</strong> #{{ $attendee->id }}</div>
                    <div><strong>Generated:</strong> {{ $generatedAt }}</div>
                    <div><span class="badge">VALID</span></div>
                </td>
            </tr>
        </table>
    </div>

    {{-- EVENT TITLE --}}
    @if($event)
        <p class="event-title">{{ $event->title ?? $event->name ?? 'Event' }}</p>
        <p class="event-sub">
            {{ $event->venue ?? '' }}
            @if(!empty($event->start_date))
                &nbsp;•&nbsp;{{ \Carbon\Carbon::parse($event->start_date)->format('d M Y, H:i') }}
            @endif
        </p>
    @endif

    {{-- ATTENDEE BOX --}}
    <div class="ticket-box">
        <table>
            <tr>
                <td style="width: 60%;">
                    <div class="label">Attendee</div>
                    <div class="value">{{ $attendee->name ?? $attendee->full_name ?? '—' }}</div>
                </td>
                <td style="width: 40%; text-align: right;">
                    <div class="label">Ticket Type</div>
                    <div class="value">{{ $ticket->name ?? $ticket->type ?? '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Email</div>
                    <div class="value mono" style="font-size: 11px;">{{ $attendee->email ?? '—' }}</div>
                </td>
                <td style="text-align: right;">
                    <div class="label">Phone</div>
                    <div class="value mono" style="font-size: 11px;">{{ $attendee->phone ?? $attendee->phone_number ?? '—' }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- DETAILS TABLE --}}
    <table class="details">
        <thead>
            <tr>
                <th style="width:40%;">Field</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Attendee ID</strong></td>
                <td class="mono">#{{ $attendee->id }}</td>
            </tr>
            @if($ticket)
                <tr>
                    <td><strong>Ticket ID</strong></td>
                    <td class="mono">#{{ $ticket->id }}</td>
                </tr>
                @if(!empty($ticket->price))
                    <tr>
                        <td><strong>Price</strong></td>
                        <td>KES {{ number_format($ticket->price, 2) }}</td>
                    </tr>
                @endif
            @endif
            @if($event)
                <tr>
                    <td><strong>Event</strong></td>
                    <td>{{ $event->title ?? $event->name ?? '—' }}</td>
                </tr>
                @if(!empty($event->venue) || !empty($event->location))
                    <tr>
                        <td><strong>Venue</strong></td>
                        <td>{{ $event->venue ?? $event->location }}</td>
                    </tr>
                @endif
                @if(!empty($event->start_date))
                    <tr>
                        <td><strong>Starts</strong></td>
                        <td>{{ \Carbon\Carbon::parse($event->start_date)->format('d M Y, H:i') }}</td>
                    </tr>
                @endif
            @endif
            <tr>
                <td><strong>Issued At</strong></td>
                <td>{{ $generatedAt }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        This ticket is valid only for the named attendee. Present this PDF (printed or on mobile) at the entrance.<br>
        &copy; {{ date('Y') }} {{ $appName }} — All rights reserved.
    </div>

</body>
</html>