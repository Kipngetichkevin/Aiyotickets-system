@include('backend.includes.header')
@include('backend.includes.Sidebar')

<main class="app-main">
    <div style="padding: 28px 32px; width: 100%; box-sizing: border-box;">

        {{-- ============ WELCOME ============ --}}
        <div style="margin-bottom: 24px;">
            <h1 style="font-size: 26px; font-weight: 700; color: #111827; margin: 0; line-height: 1.2;">
                Welcome back, {{ $user->name }}
            </h1>
            <p style="font-size: 14px; color: #6b7280; margin: 6px 0 0 0;">
                Your tickets and upcoming events
            </p>
        </div>

        {{-- ============ FLASH ============ --}}
        @if(session('error'))
            <div style="padding: 14px 18px; background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; border-radius: 10px; margin-bottom: 20px; font-size: 14px;">
                {{ session('error') }}
            </div>
        @endif

        {{-- ============ STAT CARDS ============ --}}
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 28px;" class="stat-grid">

            {{-- My Tickets --}}
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 22px; position: relative; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: #f0f9ff; border-radius: 50%;"></div>
                <div style="position: relative;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                        <div style="width: 42px; height: 42px; background: #0ea5e9; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <svg width="20" height="20" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                                <path d="M15 5v2m0 4v2m0 4v2M5 5h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V7a2 2 0 0 1 2-2z" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">My Tickets</span>
                    </div>
                    <div style="font-size: 34px; font-weight: 800; color: #111827; line-height: 1;">{{ $totalTickets }}</div>
                    <div style="font-size: 13px; color: #6b7280; margin-top: 6px;">across {{ $totalEvents }} {{ \Illuminate\Support\Str::plural('event', $totalEvents) }}</div>
                </div>
            </div>

            {{-- Upcoming --}}
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 22px; position: relative; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: #ecfdf5; border-radius: 50%;"></div>
                <div style="position: relative;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                        <div style="width: 42px; height: 42px; background: #10b981; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <svg width="20" height="20" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                <path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <span style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Upcoming</span>
                    </div>
                    <div style="font-size: 34px; font-weight: 800; color: #111827; line-height: 1;">{{ $upcomingCount }}</div>
                    <div style="font-size: 13px; color: #6b7280; margin-top: 6px;">events to attend</div>
                </div>
            </div>

            {{-- Total Spent --}}
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 22px; position: relative; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: #eef2ff; border-radius: 50%;"></div>
                <div style="position: relative;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                        <div style="width: 42px; height: 42px; background: #6366f1; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <svg width="20" height="20" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                                <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <span style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Total Spent</span>
                    </div>
                    <div style="font-size: 26px; font-weight: 800; color: #111827; line-height: 1;">KES {{ number_format($totalSpent, 0) }}</div>
                    <div style="font-size: 13px; color: #6b7280; margin-top: 6px;">on tickets</div>
                </div>
            </div>

            {{-- Rewards --}}
            <div style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); border-radius: 16px; padding: 22px; color: white; position: relative; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: rgba(255,255,255,0.12); border-radius: 50%;"></div>
                <div style="position: relative;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                        <div style="width: 42px; height: 42px; background: rgba(255,255,255,0.22); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <svg width="20" height="20" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.95;">Rewards</span>
                    </div>
                    <div style="font-size: 34px; font-weight: 800; line-height: 1;">{{ number_format($userPoints) }}</div>
                    <div style="font-size: 13px; opacity: 0.9; margin-top: 6px;">points earned</div>
                </div>
            </div>

        </div>

        {{-- ============ TWO COLUMN LAYOUT ============ --}}
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;" class="dashboard-columns">

            {{-- ============ MY TICKETS ============ --}}
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="font-size: 17px; font-weight: 700; color: #111827; margin: 0;">My Tickets</h2>
                    <a href="{{ route('events.index') }}" style="font-size: 13px; color: #0284c7; text-decoration: none; font-weight: 600;">
                        Browse events →
                    </a>
                </div>

                @if($myAttendees->count())
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @foreach($myAttendees as $attendee)
                            @php
                                $ticket = $attendee->ticket;
                                $event  = $ticket->event ?? null;
                                $statusColors = [
                                    'pending'   => ['#fef3c7', '#92400e'],
                                    'confirmed' => ['#d1fae5', '#065f46'],
                                    'used'      => ['#dbeafe', '#1e40af'],
                                    'cancelled' => ['#fee2e2', '#991b1b'],
                                ];
                                [$sBg, $sFg] = $statusColors[$attendee->status] ?? ['#f3f4f6', '#374151'];
                            @endphp

                            <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 16px; transition: border-color 0.15s;"
                                 onmouseover="this.style.borderColor='#0ea5e9';"
                                 onmouseout="this.style.borderColor='#e5e7eb';">

                                {{-- Event thumbnail --}}
                                @if($event && $event->image)
                                    <div style="width: 72px; height: 72px; border-radius: 10px; overflow: hidden; flex-shrink: 0; background: #e5e7eb;">
                                        <img src="{{ asset('storage/' . $event->image) }}"
                                             alt="{{ $event->name }}"
                                             style="width: 100%; height: 100%; object-fit: cover; display: block;">
                                    </div>
                                @else
                                    <div style="width: 72px; height: 72px; border-radius: 10px; flex-shrink: 0; background: linear-gradient(135deg, #0ea5e9, #6366f1); display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 20px;">
                                        {{ strtoupper(substr($event->name ?? 'E', 0, 1)) }}
                                    </div>
                                @endif

                                {{-- Event + ticket info --}}
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px; flex-wrap: wrap;">
                                        <h3 style="font-size: 15px; font-weight: 700; color: #111827; margin: 0;">
                                            {{ $event->name ?? 'Event' }}
                                        </h3>
                                        <span style="padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: {{ $sBg }}; color: {{ $sFg }};">
                                            {{ $attendee->status }}
                                        </span>
                                    </div>

                                    <div style="font-size: 13px; color: #6b7280; margin-bottom: 2px;">
                                        {{ $ticket->name ?? 'Ticket' }}
                                        @if($ticket && $ticket->currency)
                                            · {{ $ticket->currency }} {{ number_format($ticket->price, 0) }}
                                        @endif
                                    </div>

                                    @if($event)
                                        <div style="font-size: 12px; color: #9ca3af;">
                                            📅 {{ \Carbon\Carbon::parse($event->date)->format('d M Y, g:i A') }}
                                            · 📍 {{ $event->location }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Actions --}}
                                <div style="flex-shrink: 0; display: flex; gap: 6px;">
                                    @if($event)
                                        <a href="{{ route('event.show', $event->id) }}"
                                           style="padding: 8px 14px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 8px; font-size: 12px; font-weight: 600; white-space: nowrap;">
                                            Details
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="padding: 48px 20px; text-align: center;">
                        <div style="font-size: 48px; margin-bottom: 12px;">🎫</div>
                        <h3 style="font-size: 16px; font-weight: 700; color: #374151; margin: 0 0 6px 0;">No tickets yet</h3>
                        <p style="font-size: 13px; color: #6b7280; margin: 0 0 20px 0;">Once you buy tickets, they'll show up here.</p>
                        <a href="{{ route('events.index') }}"
                           style="display: inline-block; padding: 11px 22px; background: #0ea5e9; color: white; text-decoration: none; border-radius: 10px; font-size: 14px; font-weight: 600;">
                            Browse Events
                        </a>
                    </div>
                @endif
            </div>

            {{-- ============ UPCOMING EVENTS ============ --}}
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <h2 style="font-size: 17px; font-weight: 700; color: #111827; margin: 0 0 20px 0;">Upcoming Events</h2>

                @if($upcomingAttendees->count())
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        @foreach($upcomingAttendees as $attendee)
                            @php
                                $event = $attendee->ticket->event ?? null;
                            @endphp
                            @if($event)
                                <a href="{{ route('event.show', $event->id) }}"
                                   style="display: flex; align-items: center; gap: 12px; text-decoration: none; padding: 8px; border-radius: 10px; transition: background 0.15s;"
                                   onmouseover="this.style.background='#f9fafb';"
                                   onmouseout="this.style.background='transparent';">

                                    <div style="width: 44px; height: 44px; border-radius: 10px; background: linear-gradient(135deg, #0ea5e9, #6366f1); color: white; display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <div style="font-size: 14px; font-weight: 800; line-height: 1;">
                                            {{ \Carbon\Carbon::parse($event->date)->format('d') }}
                                        </div>
                                        <div style="font-size: 9px; text-transform: uppercase; opacity: 0.9;">
                                            {{ \Carbon\Carbon::parse($event->date)->format('M') }}
                                        </div>
                                    </div>

                                    <div style="min-width: 0;">
                                        <div style="font-size: 14px; font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ $event->name }}
                                        </div>
                                        <div style="font-size: 12px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ $event->location }}
                                        </div>
                                    </div>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div style="padding: 24px 12px; text-align: center;">
                        <div style="font-size: 32px; margin-bottom: 8px; opacity: 0.5;">📅</div>
                        <p style="font-size: 13px; color: #6b7280; margin: 0;">
                            No upcoming events.
                        </p>
                    </div>
                @endif
            </div>

        </div>

    </div>
</main>

<style>
    @media (max-width: 1200px) {
        .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
    }
    @media (max-width: 700px) {
        .stat-grid { grid-template-columns: 1fr !important; }
    }
    @media (max-width: 900px) {
        .dashboard-columns { grid-template-columns: 1fr !important; }
    }
</style>

@include('backend.includes.Footer')