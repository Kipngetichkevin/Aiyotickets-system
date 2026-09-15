@vite('resources/css/app.css')

@include('backend.includes.header')
@include('backend.includes.Sidebar')

<main style="margin-left: 300px; min-height: 100vh; background: #f9fafb; padding: 0;">
    <div style="padding: 32px 28px; max-width: 1200px;">

        {{-- ============ FLASH ============ --}}
        @if(session('success'))
            <div style="padding: 14px 18px; background: #f0f9ff; border-left: 4px solid #0ea5e9; color: #0c4a6e; border-radius: 10px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="padding: 14px 18px; background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; border-radius: 10px; margin-bottom: 20px;">
                {{ session('error') }}
            </div>
        @endif

        {{-- ============ BREADCRUMB ============ --}}
        <nav style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #6b7280; margin-bottom: 16px;">
            <a href="{{ route('dashboard') }}" style="color: #6b7280; text-decoration: none;">Dashboard</a>
            <span>/</span>
            <a href="{{ route('backend.events.show') }}" style="color: #6b7280; text-decoration: none;">Events</a>
            <span>/</span>
            <span style="color: #374151; font-weight: 500;">{{ $event->name }}</span>
        </nav>

        {{-- ============ BANNER ============ --}}
        @if($event->banner)
            <div style="width: 100%; height: 260px; border-radius: 16px; overflow: hidden; background: #e5e7eb; margin-bottom: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <img src="{{ asset('storage/' . $event->banner) }}"
                     alt="{{ $event->name }} banner"
                     style="width: 100%; height: 100%; object-fit: cover; display: block;">
            </div>
        @else
            <div style="width: 100%; height: 120px; border-radius: 16px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; margin-bottom: 28px; color: #9ca3af; font-size: 13px; border: 1px dashed #d1d5db;">
                No banner uploaded
            </div>
        @endif

        {{-- ============ EVENT HEADER ============ --}}
        <div style="display: flex; gap: 24px; align-items: flex-start; margin-bottom: 32px; flex-wrap: wrap;">

            {{-- Event Image --}}
            @if($event->image)
                <div style="width: 180px; height: 180px; border-radius: 16px; overflow: hidden; background: #e5e7eb; flex-shrink: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <img src="{{ asset('storage/' . $event->image) }}"
                         alt="{{ $event->name }}"
                         style="width: 100%; height: 100%; object-fit: cover; display: block;">
                </div>
            @else
                <div style="width: 180px; height: 180px; border-radius: 16px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #9ca3af; font-size: 12px; text-align: center; padding: 12px; border: 1px dashed #d1d5db;">
                    No image uploaded
                </div>
            @endif

            {{-- Details --}}
            <div style="flex: 1; min-width: 280px;">
                <h1 style="font-size: 28px; font-weight: 700; color: #111827; margin: 0 0 10px 0; line-height: 1.2;">
                    {{ $event->name }}
                </h1>

                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px; flex-wrap: wrap;">
                    @php
                        $statusColor = match($event->status) {
                            'active' => 'background: #d1fae5; color: #065f46;',
                            'inactive' => 'background: #f3f4f6; color: #6b7280;',
                            'cancelled' => 'background: #fee2e2; color: #991b1b;',
                            'completed' => 'background: #e0f2fe; color: #075985;',
                            default => 'background: #f3f4f6; color: #6b7280;',
                        };
                    @endphp
                    <span style="padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; {{ $statusColor }}">
                        {{ ucfirst($event->status) }}
                    </span>

                    <span style="padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; background: #f3f4f6; color: #374151;">
                        {{ $event->currency }}
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px 24px; font-size: 14px; margin-top: 20px;">
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Organizer</div>
                        <div style="font-weight: 500; color: #111827;">{{ $event->merchant?->name ?? 'No merchant assigned' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Start Date</div>
                        <div style="font-weight: 500; color: #111827;">{{ \Carbon\Carbon::parse($event->date)->format('D, M j, Y — H:i') }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Location</div>
                        <div style="font-weight: 500; color: #111827;">{{ $event->location }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">End Date</div>
                        <div style="font-weight: 500; color: #111827;">
                            {{ $event->end_date ? \Carbon\Carbon::parse($event->end_date)->format('D, M j, Y — H:i') : '—' }}
                        </div>
                    </div>
                </div>

                @if($event->description)
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                        <div style="font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Description</div>
                        <p style="font-size: 14px; color: #374151; margin: 0; line-height: 1.6;">{{ $event->description }}</p>
                    </div>
                @endif

                <div style="margin-top: 24px; display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="{{ route('backend.events.edit', $event->id) }}"
                       style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; background: #0ea5e9; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500;">
                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Edit Event
                    </a>
                    <a href="{{ route('backend.events.show') }}"
                       style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500;">
                        ← Back to Events
                    </a>
                </div>
            </div>
        </div>

        {{-- ============ STATS ============ --}}
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 32px;">
            <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 20px;">
                <p style="font-size: 11px; font-weight: 700; color: #0369a1; text-transform: uppercase; letter-spacing: 0.06em; margin: 0;">Tickets Sold</p>
                <p style="font-size: 28px; font-weight: 700; color: #0c4a6e; margin: 8px 0 0 0; line-height: 1;">{{ $ticketsSold }}</p>
            </div>
            <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 20px;">
                <p style="font-size: 11px; font-weight: 700; color: #0369a1; text-transform: uppercase; letter-spacing: 0.06em; margin: 0;">Ticket Sales</p>
                <p style="font-size: 20px; font-weight: 700; color: #0c4a6e; margin: 8px 0 0 0; line-height: 1;">KES {{ number_format($ticketSales, 2) }}</p>
            </div>
            <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 20px;">
                <p style="font-size: 11px; font-weight: 700; color: #0369a1; text-transform: uppercase; letter-spacing: 0.06em; margin: 0;">Net Sales</p>
                <p style="font-size: 20px; font-weight: 700; color: #0c4a6e; margin: 8px 0 0 0; line-height: 1;">KES {{ number_format($netSales, 2) }}</p>
            </div>
            <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 20px;">
                <p style="font-size: 11px; font-weight: 700; color: #0369a1; text-transform: uppercase; letter-spacing: 0.06em; margin: 0;">Ticket Types</p>
                <p style="font-size: 28px; font-weight: 700; color: #0c4a6e; margin: 8px 0 0 0; line-height: 1;">{{ $tickets->count() }}</p>
            </div>
        </div>

        {{-- ============ TICKET TYPES ============ --}}
        <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 24px; margin-bottom: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="font-size: 18px; font-weight: 700; color: #111827; margin: 0;">Ticket Types</h2>

                <a href="{{ route('tickets.create', $event->id) }}"
                   style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; background: #0ea5e9; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500;">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                    </svg>
                    Add New Ticket
                </a>
            </div>

            @if($tickets->isEmpty())
                <div style="padding: 40px 20px; text-align: center; color: #6b7280; font-size: 14px;">
                    No tickets available for this event.
                    <a href="{{ route('tickets.create', $event->id) }}" style="color: #0284c7; text-decoration: underline;">Add the first one</a>.
                </div>
            @else
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                    @foreach($tickets as $ticket)
                        <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0;">{{ $ticket->name }}</h3>
                                <span style="padding: 3px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; background: #e0f2fe; color: #075985;">
                                    {{ $ticket->currency }}
                                </span>
                            </div>

                            <p style="font-size: 22px; font-weight: 700; color: #0c4a6e; margin: 0 0 10px 0;">
                                {{ number_format($ticket->price, 2) }}
                            </p>

                            <div style="font-size: 13px; color: #6b7280; margin-bottom: 4px;">
                                Available: <strong style="color: #374151;">{{ $ticket->quantity }}</strong>
                            </div>

                            @if($ticket->start_date)
                                <div style="font-size: 12px; color: #6b7280;">
                                    {{ \Carbon\Carbon::parse($ticket->start_date)->format('d M Y') }}
                                    @if($ticket->end_date)
                                        — {{ \Carbon\Carbon::parse($ticket->end_date)->format('d M Y') }}
                                    @endif
                                </div>
                            @endif

                            <div style="display: flex; gap: 8px; margin-top: 16px; padding-top: 14px; border-top: 1px solid #f3f4f6;">
                                <a href="{{ route('tickets.edit', [$event->id, $ticket->id]) }}"
                                   style="flex: 1; padding: 8px 12px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 500; text-align: center;">
                                    Edit
                                </a>

                                <form method="POST" action="{{ route('tickets.destroy', [$event->id, $ticket->id]) }}"
                                      onsubmit="return confirm('Delete this ticket?');"
                                      style="flex: 1; margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            style="width: 100%; padding: 8px 12px; background: #fef2f2; color: #dc2626; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer;">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ============ ATTENDEES ============ --}}
        <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                <h2 style="font-size: 18px; font-weight: 700; color: #111827; margin: 0;">Attendees</h2>
                <div style="display: flex; gap: 8px;">
                    <a href="#" style="padding: 8px 14px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 8px; font-size: 13px; font-weight: 500;">View all</a>
                    <a href="#" style="padding: 8px 14px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 8px; font-size: 13px; font-weight: 500;">Export CSV</a>
                    <a href="#" style="padding: 8px 14px; background: #0ea5e9; color: white; text-decoration: none; border-radius: 8px; font-size: 13px; font-weight: 500;">Add Attendee</a>
                </div>
            </div>

            @if($attendees->isEmpty())
                <div style="padding: 40px 20px; text-align: center; color: #6b7280; font-size: 14px;">
                    No attendees yet for this event.
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                        <thead>
                            <tr style="background: #f0f9ff; border-bottom: 1px solid #bae6fd;">
                                <th style="padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #075985; text-transform: uppercase; letter-spacing: 0.05em;">Name</th>
                                <th style="padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #075985; text-transform: uppercase; letter-spacing: 0.05em;">Details</th>
                                <th style="padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #075985; text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendees as $attendee)
                                <tr style="border-bottom: 1px solid #f3f4f6;">
                                    <td style="padding: 10px 14px;">
                                        <div style="font-weight: 600; color: #111827;">{{ $attendee->name }}</div>
                                    </td>
                                    <td style="padding: 10px 14px;">
                                        <div style="font-size: 12px; color: #6b7280;">{{ $attendee->email ?? '—' }}</div>
                                        <div style="font-size: 12px; color: #6b7280;">{{ $attendee->phone ?? '' }}</div>
                                    </td>
                                    <td style="padding: 10px 14px;">
                                        <span style="padding: 3px 10px; border-radius: 8px; font-size: 11px; font-weight: 600;
                                            {{ $attendee->status === 'paid' ? 'background: #d1fae5; color: #065f46;' : 'background: #fef3c7; color: #92400e;' }}">
                                            {{ ucfirst($attendee->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</main>

@include('backend.includes.Footer')