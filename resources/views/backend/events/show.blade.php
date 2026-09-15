@vite('resources/css/app.css')

@include('backend.includes.header')
@include('backend.includes.Sidebar')

<style>
    .events-page {
        margin-left: 300px !important;
        min-height: 100vh !important;
        background: #f9fafb !important;
        padding: 0 !important;
        box-sizing: border-box !important;
        width: calc(100% - 300px) !important;
    }

    .events-container {
        padding: 32px 28px !important;
        max-width: 1400px !important;
        box-sizing: border-box !important;
    }

    /* Header */
    .events-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-end !important;
        gap: 16px !important;
        margin-bottom: 24px !important;
        flex-wrap: wrap !important;
    }

    .events-title {
        font-size: 28px !important;
        font-weight: 700 !important;
        color: #111827 !important;
        margin: 0 !important;
        line-height: 1.2 !important;
    }

    .events-subtitle {
        font-size: 15px !important;
        color: #6b7280 !important;
        margin: 6px 0 0 0 !important;
    }

    /* Tabs */
    .events-tabs {
        display: inline-flex !important;
        background: #ffffff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 12px !important;
        padding: 4px !important;
        gap: 2px !important;
        margin-bottom: 24px !important;
    }

    .events-tab {
        padding: 9px 18px !important;
        border-radius: 8px !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        text-decoration: none !important;
        color: #6b7280 !important;
        transition: all 0.15s !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    .events-tab:hover {
        color: #374151 !important;
        background: #f9fafb !important;
    }

    .events-tab.is-active {
        background: #0ea5e9 !important;
        color: #ffffff !important;
        font-weight: 600 !important;
    }

    /* Grid */
    .events-grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)) !important;
        gap: 20px !important;
    }

    /* Card */
    .event-card {
        background: #ffffff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 16px !important;
        overflow: hidden !important;
        transition: all 0.2s ease !important;
        display: flex !important;
        flex-direction: column !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04) !important;
    }

    .event-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08) !important;
        border-color: #bae6fd !important;
    }

    /* Card Banner */
    .event-card-banner {
        position: relative !important;
        width: 100% !important;
        height: 160px !important;
        overflow: hidden !important;
        background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%) !important;
    }

    .event-card-banner img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        display: block !important;
        transition: transform 0.3s ease !important;
    }

    .event-card:hover .event-card-banner img {
        transform: scale(1.05) !important;
    }

    /* Date badge on banner */
    .event-date-badge {
        position: absolute !important;
        top: 12px !important;
        left: 12px !important;
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(8px) !important;
        border-radius: 10px !important;
        padding: 8px 12px !important;
        text-align: center !important;
        min-width: 52px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
    }

    .event-date-day {
        display: block !important;
        font-size: 20px !important;
        font-weight: 700 !important;
        color: #0c4a6e !important;
        line-height: 1 !important;
    }

    .event-date-month {
        display: block !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #0369a1 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        margin-top: 3px !important;
    }

    /* Status pill on banner */
    .event-status-pill {
        position: absolute !important;
        top: 12px !important;
        right: 12px !important;
        padding: 5px 12px !important;
        border-radius: 999px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
    }

    /* Card Body */
    .event-card-body {
        padding: 18px !important;
        flex: 1 !important;
        display: flex !important;
        flex-direction: column !important;
    }

    .event-card-name {
        font-size: 17px !important;
        font-weight: 700 !important;
        color: #111827 !important;
        margin: 0 0 8px 0 !important;
        line-height: 1.3 !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
    }

    .event-card-meta {
        display: flex !important;
        flex-direction: column !important;
        gap: 6px !important;
        margin-bottom: 16px !important;
        flex: 1 !important;
    }

    .event-card-meta-row {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        font-size: 13px !important;
        color: #6b7280 !important;
        line-height: 1.4 !important;
    }

    .event-card-meta-row svg {
        width: 14px !important;
        height: 14px !important;
        flex-shrink: 0 !important;
        color: #9ca3af !important;
    }

    .event-card-meta-row strong {
        color: #374151 !important;
        font-weight: 500 !important;
    }

    /* Card Actions */
    .event-card-actions {
        display: flex !important;
        gap: 8px !important;
        padding-top: 14px !important;
        border-top: 1px solid #f3f4f6 !important;
    }

    .event-btn-primary {
        flex: 1 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        padding: 9px 14px !important;
        background: #0ea5e9 !important;
        color: #ffffff !important;
        text-decoration: none !important;
        border-radius: 8px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        border: none !important;
        cursor: pointer !important;
        transition: background 0.15s !important;
    }

    .event-btn-primary:hover {
        background: #0284c7 !important;
    }

    .event-btn-secondary {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 9px 12px !important;
        background: #f3f4f6 !important;
        color: #374151 !important;
        text-decoration: none !important;
        border-radius: 8px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        border: none !important;
        cursor: pointer !important;
        transition: background 0.15s !important;
    }

    .event-btn-secondary:hover {
        background: #e5e7eb !important;
    }

    /* Empty state */
    .events-empty {
        padding: 80px 30px !important;
        text-align: center !important;
        background: #ffffff !important;
        border: 2px dashed #e5e7eb !important;
        border-radius: 16px !important;
    }

    .events-empty-icon {
        width: 72px !important;
        height: 72px !important;
        background: #f0f9ff !important;
        border-radius: 50% !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin-bottom: 20px !important;
    }

    .events-empty-title {
        font-size: 18px !important;
        font-weight: 600 !important;
        color: #374151 !important;
        margin: 0 0 6px 0 !important;
    }

    .events-empty-text {
        font-size: 14px !important;
        color: #6b7280 !important;
        margin: 0 0 24px 0 !important;
    }

    /* Mobile */
    @media (max-width: 1023px) {
        .events-page {
            margin-left: 0 !important;
            width: 100% !important;
        }
    }
</style>

<main class="events-page">
    <div class="events-container">

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

        {{-- ============ HEADER ============ --}}
        <div class="events-header">
            <div>
                <h1 class="events-title">Your Events</h1>
                <p class="events-subtitle">Manage your events, tickets, and attendees.</p>
            </div>

            <a href="{{ route('backend.events.create') }}"
               style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 22px; background: #0ea5e9; color: white; text-decoration: none; border-radius: 10px; font-size: 15px; font-weight: 600; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                </svg>
                Add Event
            </a>
        </div>

        {{-- ============ TABS ============ --}}
        <div class="events-tabs">
            <a href="{{ route('backend.events.show') }}" class="events-tab is-active">
                <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                Upcoming
            </a>
            <a href="{{ route('backend.events.past') }}" class="events-tab">
                <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
                Past Events
            </a>
        </div>

        {{-- ============ GRID ============ --}}
        @if($events->count())

            <div class="events-grid">
                @foreach($events as $event)
                    @php
                        // Determine which image to use for the banner
                        $bannerSrc = null;
                        if ($event->banner) {
                            $bannerSrc = asset('storage/' . $event->banner);
                        } elseif ($event->image) {
                            $bannerSrc = asset('storage/' . $event->image);
                        }

                        $eventDate = \Carbon\Carbon::parse($event->date);

                        $statusStyle = match($event->status) {
                            'active' => 'background: #10b981; color: white;',
                            'inactive' => 'background: #6b7280; color: white;',
                            'cancelled' => 'background: #ef4444; color: white;',
                            'completed' => 'background: #0ea5e9; color: white;',
                            default => 'background: #6b7280; color: white;',
                        };
                    @endphp

                    <div class="event-card">

                        {{-- Banner --}}
                        <div class="event-card-banner">
                            @if($bannerSrc)
                                <img src="{{ $bannerSrc }}" alt="{{ $event->name }}">
                            @endif

                            {{-- Date badge --}}
                            <div class="event-date-badge">
                                <span class="event-date-day">{{ $eventDate->format('d') }}</span>
                                <span class="event-date-month">{{ strtoupper($eventDate->format('M')) }}</span>
                            </div>

                            {{-- Status pill --}}
                            <span class="event-status-pill" style="{{ $statusStyle }}">
                                {{ ucfirst($event->status) }}
                            </span>
                        </div>

                        {{-- Body --}}
                        <div class="event-card-body">
                            <h3 class="event-card-name">{{ $event->name }}</h3>

                            <div class="event-card-meta">
                                <div class="event-card-meta-row">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                    </svg>
                                    <strong>{{ $event->merchant?->name ?? 'No organizer' }}</strong>
                                </div>

                                <div class="event-card-meta-row">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path d="M12 6v6l4 2"/>
                                    </svg>
                                    {{ $eventDate->format('D, M j, Y · H:i') }}
                                </div>

                                <div class="event-card-meta-row">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                        <circle cx="12" cy="9" r="2.5"/>
                                    </svg>
                                    {{ $event->location }}
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="event-card-actions">
                                <a href="{{ route('backend.events.page', $event->id) }}" class="event-btn-primary">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    Manage
                                </a>

                                <a href="{{ route('backend.events.edit', $event->id) }}" class="event-btn-secondary" title="Edit">
                                    <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>

                                <form method="POST" action="{{ route('backend.events.delete', $event->id) }}"
                                      onsubmit="return confirm('Delete this event and all its tickets?');"
                                      style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="event-btn-secondary" title="Delete" style="color: #dc2626;">
                                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($events->hasPages())
                <div style="margin-top: 32px;">{{ $events->withQueryString()->links() }}</div>
            @endif

        @else

            {{-- ============ EMPTY STATE ============ --}}
            <div class="events-empty">
                <div class="events-empty-icon">
                    <svg style="width: 36px; height: 36px; color: #0ea5e9;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                </div>
                <p class="events-empty-title">No upcoming events</p>
                <p class="events-empty-text">Create your first event to get started.</p>
                <a href="{{ route('backend.events.create') }}"
                   style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 22px; background: #0ea5e9; color: white; text-decoration: none; border-radius: 10px; font-size: 15px; font-weight: 600;">
                    <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                    </svg>
                    Add Event
                </a>
            </div>

        @endif
    </div>
</main>

@include('backend.includes.Footer')