@include('backend.includes.header')
@include('backend.includes.Sidebar')

<main class="app-main">
    <div style="padding: 28px 32px; width: 100%; box-sizing: border-box;">

        {{-- ============ WELCOME ============ --}}
        <div style="margin-bottom: 24px;">
            <h1 style="font-size: 26px; font-weight: 700; color: #111827; margin: 0; line-height: 1.2;">
                Welcome back, {{ auth()->user()->name }}
            </h1>
            <p style="font-size: 14px; color: #6b7280; margin: 6px 0 0 0;">
                Here's what's happening with your events today.
            </p>
        </div>

        {{-- ============ STAT CARDS ============ --}}
        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; margin-bottom: 28px;" class="stat-grid">

            {{-- Total Events --}}
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; position: relative; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: #f0f9ff; border-radius: 50%;"></div>
                <div style="position: relative;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                        <div style="width: 42px; height: 42px; background: #0ea5e9; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg style="width: 20px; height: 20px; color: white;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                <path d="M16 2v4M8 2v4M3 10h18"/>
                            </svg>
                        </div>
                        <span style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Total Events</span>
                    </div>
                    <div style="font-size: 34px; font-weight: 800; color: #111827; line-height: 1;">{{ $totalEvents }}</div>
                    <div style="font-size: 13px; color: #6b7280; margin-top: 6px;">{{ $activeEvents }} active</div>
                </div>
            </div>

            {{-- Tickets Sold --}}
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; position: relative; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: #ecfdf5; border-radius: 50%;"></div>
                <div style="position: relative;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                        <div style="width: 42px; height: 42px; background: #10b981; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg style="width: 20px; height: 20px; color: white;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M15 5v2m0 4v2m0 4v2M5 5h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V7a2 2 0 0 1 2-2z"/>
                            </svg>
                        </div>
                        <span style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Tickets Sold</span>
                    </div>
                    <div style="font-size: 34px; font-weight: 800; color: #111827; line-height: 1;">{{ $totalTicketsSold }}</div>
                    <div style="font-size: 13px; color: #6b7280; margin-top: 6px;">across all events</div>
                </div>
            </div>

            {{-- Revenue --}}
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; position: relative; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: #eef2ff; border-radius: 50%;"></div>
                <div style="position: relative;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                        <div style="width: 42px; height: 42px; background: #6366f1; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg style="width: 20px; height: 20px; color: white;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                        </div>
                        <span style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Revenue</span>
                    </div>
                    <div style="font-size: 26px; font-weight: 800; color: #111827; line-height: 1;">KES {{ number_format($totalRevenue, 0) }}</div>
                    <div style="font-size: 13px; color: #6b7280; margin-top: 6px;">total earnings</div>
                </div>
            </div>

            {{-- Rewards --}}
            <div style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); border-radius: 16px; padding: 24px; color: white; position: relative; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: rgba(255,255,255,0.12); border-radius: 50%;"></div>
                <div style="position: relative;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                        <div style="width: 42px; height: 42px; background: rgba(255,255,255,0.22); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg style="width: 20px; height: 20px; color: white;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <span style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.95;">Rewards</span>
                    </div>
                    <div style="font-size: 34px; font-weight: 800; line-height: 1;">{{ number_format($userPoints) }}</div>
                    <div style="font-size: 13px; opacity: 0.9; margin-top: 6px;">5% back on purchases</div>
                </div>
            </div>

        </div>

        {{-- ============ CHARTS ============ --}}
        @include('backend.includes.Charts')

        {{-- ============ TWO COLUMN LAYOUT ============ --}}
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;" class="dashboard-columns">

            {{-- Upcoming Events --}}
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="font-size: 17px; font-weight: 700; color: #111827; margin: 0;">Upcoming Events</h2>
                    <a href="{{ route('backend.events.show') }}" style="font-size: 13px; color: #0284c7; text-decoration: none; font-weight: 600;">
                        View all →
                    </a>
                </div>

                @if($upcomingEvents->count())
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @foreach($upcomingEvents as $event)
                            <a href="{{ route('backend.events.page', $event->id) }}"
                               style="display: flex; align-items: center; gap: 16px; padding: 14px; background: #f9fafb; border-radius: 12px; text-decoration: none; transition: background 0.15s;"
                               onmouseover="this.style.background='#f0f9ff';"
                               onmouseout="this.style.background='#f9fafb';">

                                <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #0ea5e9, #6366f1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; flex-shrink: 0; text-align: center;">
                                    <div>
                                        <div style="font-size: 18px; line-height: 1;">{{ \Carbon\Carbon::parse($event->date)->format('d') }}</div>
                                        <div style="font-size: 10px; text-transform: uppercase; opacity: 0.9; letter-spacing: 0.04em;">{{ \Carbon\Carbon::parse($event->date)->format('M') }}</div>
                                    </div>
                                </div>

                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $event->name }}
                                    </div>
                                    <div style="font-size: 13px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $event->location }}
                                    </div>
                                </div>

                                <span style="padding: 8px 16px; background: #0ea5e9; color: white; border-radius: 8px; font-size: 13px; font-weight: 600; white-space: nowrap;">
                                    Manage
                                </span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div style="padding: 40px 20px; text-align: center;">
                        <div style="font-size: 40px; margin-bottom: 12px;">📅</div>
                        <p style="font-size: 14px; color: #6b7280; margin: 0 0 16px 0;">No upcoming events.</p>
                        <a href="{{ route('backend.events.create') }}"
                           style="display: inline-block; padding: 10px 20px; background: #0ea5e9; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600;">
                            Create Your First Event
                        </a>
                    </div>
                @endif
            </div>

            {{-- Recent Activity --}}
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <h2 style="font-size: 17px; font-weight: 700; color: #111827; margin: 0 0 20px 0;">Recent Activity</h2>

                @if($recentAttendees->count())
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        @foreach($recentAttendees as $attendee)
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 36px; height: 36px; background: #f0f9ff; color: #0369a1; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; flex-shrink: 0;">
                                    {{ strtoupper(substr($attendee->name, 0, 1)) }}
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 14px; font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $attendee->name }}
                                    </div>
                                    <div style="font-size: 12px; color: #6b7280;">
                                        {{ $attendee->created_at ? $attendee->created_at->diffForHumans() : 'Just now' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="padding: 24px 12px; text-align: center;">
                        <div style="font-size: 32px; margin-bottom: 8px; opacity: 0.5;">📊</div>
                        <p style="font-size: 13px; color: #6b7280; margin: 0;">
                            No activity yet.
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

{{-- Render scripts pushed by Charts --}}
@stack('scripts')

@include('backend.includes.Footer')