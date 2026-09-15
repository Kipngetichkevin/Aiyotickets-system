<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aiyo Tickets — Discover Events Near You</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="margin: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: #ffffff;">

{{-- ============ NAVIGATION ============ --}}
<nav style="position: sticky; top: 0; z-index: 100; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-bottom: 1px solid #e5e7eb;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between;">
        <a href="{{ route('home') }}" style="display: flex; align-items: center; text-decoration: none;">
            <img src="{{ asset('images/logo.svg') }}" alt="Aiyo Tickets" style="height: 40px; width: auto; max-width: 160px; display: block;">
        </a>

        <div style="display: flex; align-items: center; gap: 8px;">
            @auth
                <a href="{{ route('dashboard') }}" style="padding: 9px 18px; background: #0ea5e9; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600;">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" style="padding: 9px 18px; color: #374151; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 500;">
                    Log in
                </a>
                <a href="{{ route('register') }}" style="padding: 9px 18px; background: #0ea5e9; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600;">
                    Sign up
                </a>
            @endauth
        </div>
    </div>
</nav>

{{-- ============ HERO ============ --}}
<section style="background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%); color: white; padding: 80px 24px; text-align: center;">
    <div style="max-width: 720px; margin: 0 auto;">
        <h1 style="font-size: 48px; font-weight: 800; margin: 0 0 16px 0; line-height: 1.1; letter-spacing: -0.02em;">
            Discover Events Near You
        </h1>
        <p style="font-size: 18px; opacity: 0.95; margin: 0 0 32px 0; line-height: 1.5;">
            Zero hidden fees. Secure M-Pesa payments. Instant ticket delivery to your phone.
        </p>

        <form method="GET" action="{{ route('home') }}" style="display: flex; gap: 8px; background: white; padding: 8px; border-radius: 14px; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search events, venues, or cities..."
                   style="flex: 1; padding: 14px 18px; border: none; outline: none; font-size: 16px; background: transparent; color: #111827;">
            <button type="submit"
                    style="padding: 14px 28px; background: #0ea5e9; color: white; border: none; border-radius: 10px; font-size: 15px; font-weight: 700; cursor: pointer;">
                Search
            </button>
        </form>
    </div>
</section>

{{-- ============ TRUST BADGES ============ --}}
<section style="padding: 40px 24px; background: white; border-bottom: 1px solid #e5e7eb;">
    <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px;">
        <div style="text-align: center;">
            <div style="font-size: 28px; margin-bottom: 8px;">🔒</div>
            <div style="font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 3px;">Secure Payments</div>
            <div style="font-size: 13px; color: #6b7280;">M-Pesa, cards & more</div>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 28px; margin-bottom: 8px;">⚡</div>
            <div style="font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 3px;">Instant Delivery</div>
            <div style="font-size: 13px; color: #6b7280;">QR tickets to your phone</div>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 28px; margin-bottom: 8px;">💰</div>
            <div style="font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 3px;">Zero Hidden Fees</div>
            <div style="font-size: 13px; color: #6b7280;">What you see is what you pay</div>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 28px; margin-bottom: 8px;">🎁</div>
            <div style="font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 3px;">Earn Rewards</div>
            <div style="font-size: 13px; color: #6b7280;">Get points on every purchase</div>
        </div>
    </div>
</section>

{{-- ============ CATEGORY TABS ============ --}}
<div style="padding: 28px 24px; background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; gap: 8px; flex-wrap: wrap; justify-content: center;">
        <a href="{{ route('home') }}"
           style="padding: 9px 20px; border-radius: 999px; text-decoration: none; font-size: 14px; font-weight: 600;
                  {{ !request('type') ? 'background: #0ea5e9; color: white; box-shadow: 0 1px 3px rgba(14,165,233,0.3);' : 'background: white; color: #374151; border: 1px solid #e5e7eb;' }}">
            All Events
        </a>
        @foreach($eventTypes as $type)
            <a href="{{ route('home', ['type' => $type->id]) }}"
               style="padding: 9px 20px; border-radius: 999px; text-decoration: none; font-size: 14px; font-weight: 600;
                      {{ request('type') == $type->id ? 'background: #0ea5e9; color: white; box-shadow: 0 1px 3px rgba(14,165,233,0.3);' : 'background: white; color: #374151; border: 1px solid #e5e7eb;' }}">
                {{ $type->name }}
            </a>
        @endforeach
    </div>
</div>

{{-- ============ EVENTS GRID ============ --}}
<section style="padding: 48px 24px; background: #f9fafb;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 28px;">
            <h2 style="font-size: 28px; font-weight: 800; color: #111827; margin: 0; letter-spacing: -0.01em;">
                {{ request('type') && $eventTypes->find(request('type')) ? $eventTypes->find(request('type'))->name . ' Events' : 'Upcoming Events' }}
            </h2>
            @if(request()->hasAny(['search', 'type']))
                <a href="{{ route('home') }}" style="font-size: 14px; color: #0284c7; text-decoration: none; font-weight: 600;">
                    Clear filters
                </a>
            @endif
        </div>

        @if($events->count())
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
                @foreach($events as $event)
                    @php
                        $bannerSrc = $event->banner
                            ? asset('storage/' . $event->banner)
                            : ($event->image ? asset('storage/' . $event->image) : null);
                        $eventDate = \Carbon\Carbon::parse($event->date);
                        $minPrice = $event->tickets->min('price');
                    @endphp

                    <a href="{{ route('event.show', $event->id) }}"
                       style="background: white; border-radius: 16px; overflow: hidden; text-decoration: none; display: block;
                              box-shadow: 0 1px 3px rgba(0,0,0,0.06); transition: transform 0.2s ease, box-shadow 0.2s ease;"
                       onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 32px rgba(0,0,0,0.1)';"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.06)';">

                        <div style="position: relative; height: 180px; background: linear-gradient(135deg, #0ea5e9, #6366f1); overflow: hidden;">
                            @if($bannerSrc)
                                <img src="{{ $bannerSrc }}" alt="{{ $event->name }}"
                                     style="width: 100%; height: 100%; object-fit: cover; display: block;">
                            @endif

                            <div style="position: absolute; top: 12px; left: 12px; background: rgba(255,255,255,0.96); backdrop-filter: blur(8px); border-radius: 10px; padding: 8px 12px; text-align: center; min-width: 52px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                <div style="font-size: 20px; font-weight: 800; color: #0c4a6e; line-height: 1;">{{ $eventDate->format('d') }}</div>
                                <div style="font-size: 11px; font-weight: 700; color: #0369a1; text-transform: uppercase; margin-top: 2px; letter-spacing: 0.04em;">{{ strtoupper($eventDate->format('M')) }}</div>
                            </div>

                            @if($event->eventType)
                                <span style="position: absolute; top: 12px; right: 12px; padding: 5px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; background: rgba(255,255,255,0.96); color: #0369a1; text-transform: uppercase; letter-spacing: 0.05em;">
                                    {{ $event->eventType->name }}
                                </span>
                            @endif
                        </div>

                        <div style="padding: 20px;">
                            <h3 style="font-size: 17px; font-weight: 700; color: #111827; margin: 0 0 12px 0; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 44px;">
                                {{ $event->name }}
                            </h3>

                            <div style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; color: #6b7280; margin-bottom: 14px;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <svg style="width: 14px; height: 14px; flex-shrink: 0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                        <circle cx="12" cy="9" r="2.5"/>
                                    </svg>
                                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $event->location }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <svg style="width: 14px; height: 14px; flex-shrink: 0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path d="M12 6v6l4 2"/>
                                    </svg>
                                    <span>{{ $eventDate->format('D, M j · H:i') }}</span>
                                </div>
                            </div>

                            @if($minPrice)
                                <div style="padding-top: 14px; border-top: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 12px; color: #6b7280;">From</span>
                                    <span style="font-size: 18px; font-weight: 800; color: #0c4a6e;">
                                        {{ $event->currency }} {{ number_format($minPrice, 0) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <div style="margin-top: 32px;">
                {{ $events->withQueryString()->links() }}
            </div>
        @else
            <div style="padding: 80px 24px; text-align: center; background: white; border-radius: 16px; border: 2px dashed #e5e7eb;">
                <div style="font-size: 48px; margin-bottom: 16px;">🎭</div>
                <h3 style="font-size: 18px; font-weight: 700; color: #374151; margin: 0 0 8px 0;">No events found</h3>
                <p style="font-size: 14px; color: #6b7280; margin: 0;">
                    @if(request('search') || request('type'))
                        Try a different search or <a href="{{ route('home') }}" style="color: #0284c7; text-decoration: none; font-weight: 600;">browse all events</a>.
                    @else
                        Check back soon for upcoming events.
                    @endif
                </p>
            </div>
        @endif
    </div>
</section>

{{-- ============ HOW IT WORKS ============ --}}
<section style="padding: 64px 24px; background: white;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h2 style="font-size: 28px; font-weight: 800; color: #111827; margin: 0 0 12px 0; text-align: center; letter-spacing: -0.01em;">
            How It Works
        </h2>
        <p style="font-size: 15px; color: #6b7280; margin: 0 0 48px 0; text-align: center;">
            Get your tickets in three simple steps
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 32px;">
            <div style="text-align: center;">
                <div style="width: 64px; height: 64px; background: #f0f9ff; border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                    <span style="font-size: 28px; font-weight: 800; color: #0ea5e9;">1</span>
                </div>
                <h3 style="font-size: 17px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Find Your Event</h3>
                <p style="font-size: 14px; color: #6b7280; margin: 0; line-height: 1.6;">
                    Browse upcoming events by category or search for your favorite.
                </p>
            </div>
            <div style="text-align: center;">
                <div style="width: 64px; height: 64px; background: #f0f9ff; border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                    <span style="font-size: 28px; font-weight: 800; color: #0ea5e9;">2</span>
                </div>
                <h3 style="font-size: 17px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Pay with M-Pesa</h3>
                <p style="font-size: 14px; color: #6b7280; margin: 0; line-height: 1.6;">
                    Secure payment straight from your phone. No hidden fees.
                </p>
            </div>
            <div style="text-align: center;">
                <div style="width: 64px; height: 64px; background: #f0f9ff; border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                    <span style="font-size: 28px; font-weight: 800; color: #0ea5e9;">3</span>
                </div>
                <h3 style="font-size: 17px; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Get Your QR Ticket</h3>
                <p style="font-size: 14px; color: #6b7280; margin: 0; line-height: 1.6;">
                    Receive your ticket instantly. Just show the QR code at the gate.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ============ CTA SECTION ============ --}}
<section style="padding: 64px 24px; background: linear-gradient(135deg, #0c4a6e 0%, #0ea5e9 100%); color: white;">
    <div style="max-width: 800px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 32px; font-weight: 800; margin: 0 0 12px 0; letter-spacing: -0.01em;">
            Organize Your Own Event
        </h2>
        <p style="font-size: 16px; opacity: 0.95; margin: 0 0 32px 0; line-height: 1.6;">
            Sell tickets, track sales, and manage attendees — all in one place.
        </p>
        @auth
            <a href="{{ route('backend.events.create') }}"
               style="display: inline-block; padding: 14px 32px; background: white; color: #0c4a6e; text-decoration: none; border-radius: 10px; font-size: 15px; font-weight: 700;">
                Create an Event
            </a>
        @else
            <a href="{{ route('register') }}"
               style="display: inline-block; padding: 14px 32px; background: white; color: #0c4a6e; text-decoration: none; border-radius: 10px; font-size: 15px; font-weight: 700;">
                Get Started Free
            </a>
        @endauth
    </div>
</section>

{{-- ============ FOOTER ============ --}}
<footer style="padding: 48px 24px 32px; background: #111827; color: #9ca3af;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 32px; margin-bottom: 32px;">
            <div>
                <img src="{{ asset('images/logo.svg') }}" alt="Aiyo Tickets" style="height: 40px; margin-bottom: 12px; filter: brightness(0) invert(1);">
                <p style="font-size: 13px; line-height: 1.6; margin: 0;">
                    Kenya's simplest event ticketing platform. Zero hidden fees, instant delivery.
                </p>
            </div>
            <div>
                <h4 style="font-size: 13px; font-weight: 700; color: white; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 12px 0;">Explore</h4>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                    <li><a href="{{ route('home') }}" style="color: #9ca3af; text-decoration: none; font-size: 14px;">All Events</a></li>
                    <li><a href="{{ route('terms') }}" style="color: #9ca3af; text-decoration: none; font-size: 14px;">Terms</a></li>
                </ul>
            </div>
            <div>
                <h4 style="font-size: 13px; font-weight: 700; color: white; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 12px 0;">For Organizers</h4>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                    <li><a href="{{ route('register') }}" style="color: #9ca3af; text-decoration: none; font-size: 14px;">Create Event</a></li>
                    <li><a href="{{ route('login') }}" style="color: #9ca3af; text-decoration: none; font-size: 14px;">Sign In</a></li>
                </ul>
            </div>
        </div>
        <div style="padding-top: 24px; border-top: 1px solid #374151; text-align: center; font-size: 13px;">
            © {{ date('Y') }} Aiyo Tickets. All rights reserved.
        </div>
    </div>
</footer>

</body>
</html>