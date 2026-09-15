@php
$navItems = [
    [
        'type' => 'link',
        'route' => 'dashboard',
        'icon' => [
            'paths' => [
                '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />',
                '<polyline points="9 22 9 12 15 12 15 22" />'
            ]
        ],
        'title' => 'Dashboard'
    ],
    [
        'type' => 'dropdown',
        'title' => 'Events',
        'icon' => [
            'paths' => [
                '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />',
                '<circle cx="9" cy="7" r="4" />',
                '<path d="M22 21v-2a4 4 0 0 0-3-3.87" />',
                '<path d="M16 3.13a4 4 0 0 1 0 7.75" />'
            ]
        ],
        'children' => [
            ['route' => 'backend.events.create', 'title' => 'Create Event'],
            ['route' => 'backend.events.show',   'title' => 'All Events'],
        ]
    ],
    [
        'type' => 'dropdown',
        'title' => 'Finance',
        'icon' => [
            'paths' => [
                '<rect width="20" height="14" x="2" y="7" rx="2" ry="2" />',
                '<path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />'
            ]
        ],
        'children' => [
            ['route' => 'backend.payees.index',  'title' => 'All Payees'],
            ['route' => 'backend.payees.index',  'title' => 'Suppliers',  'query' => ['category' => 'supplier']],
            ['route' => 'backend.payees.index',  'title' => 'Personnel',  'query' => ['category' => 'personnel']],
            ['route' => 'backend.payees.index',  'title' => 'Creditors',  'query' => ['category' => 'creditor']],
            ['route' => 'backend.payouts.index', 'title' => 'Payouts'],
        ]
    ],
    [
        'type' => 'link',
        'route' => 'backend.reports',
        'icon' => [
            'paths' => [
                '<rect width="18" height="18" x="3" y="4" rx="2" ry="2" />',
                '<line x1="16" x2="16" y1="2" y2="6" />',
                '<line x1="8" x2="8" y1="2" y2="6" />',
                '<line x1="3" x2="21" y1="10" y2="10" />',
                '<path d="M8 14h.01" />',
                '<path d="M12 14h.01" />',
                '<path d="M16 14h.01" />',
                '<path d="M8 18h.01" />',
                '<path d="M12 18h.01" />',
                '<path d="M16 18h.01" />'
            ]
        ],
        'title' => 'Reports'
    ],
    [
        'type' => 'link',
        'route' => 'backend.calendar.index',
        'icon' => [
            'paths' => [
                '<rect width="18" height="18" x="3" y="4" rx="2" ry="2" />',
                '<line x1="16" x2="16" y1="2" y2="6" />',
                '<line x1="8" x2="8" y1="2" y2="6" />',
                '<line x1="3" x2="21" y1="10" y2="10" />',
                '<path d="M8 14h.01" />',
                '<path d="M12 14h.01" />',
                '<path d="M16 14h.01" />',
                '<path d="M8 18h.01" />',
                '<path d="M12 18h.01" />',
                '<path d="M16 18h.01" />'
            ]
        ],
        'title' => 'Calendar'
    ],
    [
        'type' => 'link',
        'route' => 'backend.documentation.index',
        'icon' => [
            'paths' => [
                '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />',
                '<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />'
            ]
        ],
        'title' => 'Documentation'
    ],
    [
        'type' => 'link',
        'route' => 'backend.qrcode.index',
        'icon' => [
            'paths' => [
                '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />',
                '<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />'
            ]
        ],
        'title' => 'QR Code'
    ]
];

// Precompute route existence once per page load — avoids repeated Route::has() calls
$routeChecks = [];
foreach ($navItems as $item) {
    if ($item['type'] === 'dropdown') {
        foreach ($item['children'] as $child) {
            $routeChecks[$child['route']] = \Illuminate\Support\Facades\Route::has($child['route']);
        }
    } else {
        $routeChecks[$item['route']] = \Illuminate\Support\Facades\Route::has($item['route']);
    }
}
@endphp

<style>
    /* ============================================
       SIDEBAR NAV — NATIVE <details> DROPDOWNS
       No Preline, no JS, no animation lag.
       ============================================ */

    .sidebar-nav details > summary {
        list-style: none;
        cursor: pointer;
        outline: none;
        user-select: none;
    }
    .sidebar-nav details > summary::-webkit-details-marker {
        display: none;
    }
    .sidebar-nav details > summary::marker {
        display: none;
    }

    /* Chevron swap: show down when closed, up when open */
    .sidebar-nav details > summary .chev-down { display: inline-block; }
    .sidebar-nav details > summary .chev-up   { display: none; }
    .sidebar-nav details[open] > summary .chev-down { display: none; }
    .sidebar-nav details[open] > summary .chev-up   { display: inline-block; }

    /* Links — instant hover, no transitions */
    .sidebar-nav a { text-decoration: none; }

    /* Submenu — instant reveal */
    .sidebar-nav details > ul {
        margin: 4px 0 0 0;
        padding: 0 0 0 32px;
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    /* Hover states */
    .sidebar-nav .nav-link:hover { background: #f3f4f6; }
    .sidebar-nav .nav-link.active { background: #e0f2fe; color: #0369a1; font-weight: 600; }
</style>

<!-- ========== SIDEBAR ========== -->
<div id="hs-application-sidebar"
     style="
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: auto !important;
        bottom: 0 !important;
        width: 260px !important;
        min-width: 260px !important;
        max-width: 260px !important;
        height: 100vh !important;
        max-height: 100vh !important;
        transform: none !important;
        transition: none !important;
        display: block !important;
        overflow: hidden !important;
        z-index: 40 !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        border-right: 1px solid #e5e7eb !important;
        visibility: visible !important;
        opacity: 1 !important;
     ">

    <div style="display: flex; flex-direction: column; height: 100vh; width: 100%;">

        {{-- ============ BRAND CARD ============ --}}
        <div style="padding: 20px 16px 16px 16px; border-bottom: 1px solid #f1f5f9; flex-shrink: 0;">
            <a href="/" style="
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    text-decoration: none;
                    padding: 20px 12px;
                    border-radius: 14px;
                    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
                    border: 1px solid #e0f2fe;
                ">
                <img src="{{ asset('images/logo.svg') }}"
                     alt="Aiyo Tickets"
                     style="height: 56px; width: auto; max-width: 160px; display: block; margin-bottom: 10px;">
                <div style="text-align: center; line-height: 1.2;">
                    <div style="font-size: 15px; font-weight: 700; color: #0c4a6e;">Aiyo Tickets</div>
                    <div style="font-size: 11px; color: #0369a1; margin-top: 2px; letter-spacing: 0.02em;">Event Platform</div>
                </div>
            </a>
        </div>

        {{-- ============ NAVIGATION ============ --}}
        <div style="flex: 1 1 auto; overflow-y: auto; overflow-x: hidden; padding: 12px;">
            <nav class="sidebar-nav">
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px;">

                    @foreach($navItems as $item)
                        @php
                            $isActive = false;
                            if ($item['type'] === 'dropdown') {
                                foreach ($item['children'] as $child) {
                                    if (request()->routeIs($child['route'])) { $isActive = true; break; }
                                }
                            } else {
                                $isActive = request()->routeIs($item['route']);
                            }
                            $iconMarkup = '';
                            foreach ($item['icon']['paths'] as $path) { $iconMarkup .= $path; }
                        @endphp

                        @if($item['type'] === 'link')
                            @if($routeChecks[$item['route']] ?? false)
                                <li>
                                    <a class="nav-link {{ $isActive ? 'active' : '' }}"
                                       href="{{ route($item['route']) }}"
                                       style="display: flex; align-items: center; gap: 14px; padding: 10px 12px; border-radius: 8px; font-size: 14px; color: {{ $isActive ? '#0369a1' : '#374151' }};">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                                            {!! $iconMarkup !!}
                                        </svg>
                                        {{ $item['title'] }}
                                    </a>
                                </li>
                            @endif

                        @elseif($item['type'] === 'dropdown')
                            <li>
                                <details {{ $isActive ? 'open' : '' }}>
                                    <summary style="display: flex; align-items: center; gap: 14px; padding: 10px 12px; border-radius: 8px; font-size: 14px; color: {{ $isActive ? '#0369a1' : '#374151' }}; {{ $isActive ? 'background: #e0f2fe; font-weight: 600;' : '' }}"
                                             onmouseover="if(!this.parentElement.open) this.style.background='#f3f4f6'"
                                             onmouseout="if(!this.parentElement.open) this.style.background='transparent'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                                            {!! $iconMarkup !!}
                                        </svg>
                                        <span style="flex: 1;">{{ $item['title'] }}</span>

                                        {{-- Down chevron (closed state) --}}
                                        <svg class="chev-down" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                                            <path d="m6 9 6 6 6-6" />
                                        </svg>

                                        {{-- Up chevron (open state) --}}
                                        <svg class="chev-up" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                                            <path d="m18 15-6-6-6 6" />
                                        </svg>
                                    </summary>

                                    <ul>
                                        @foreach($item['children'] as $child)
                                            @if($routeChecks[$child['route']] ?? false)
                                                @php
                                                    $childActive = request()->routeIs($child['route']);
                                                    $childHref = route($child['route'], $child['query'] ?? []);
                                                @endphp
                                                <li>
                                                    <a class="nav-link {{ $childActive ? 'active' : '' }}"
                                                       href="{{ $childHref }}"
                                                       style="display: flex; align-items: center; padding: 8px 12px; border-radius: 8px; font-size: 13.5px; color: {{ $childActive ? '#0369a1' : '#4b5563' }};">
                                                        {{ $child['title'] }}
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </details>
                            </li>
                        @endif
                    @endforeach

                </ul>
            </nav>
        </div>

        {{-- ============ USER CARD ============ --}}
        <div style="flex-shrink: 0; padding: 12px 16px; border-top: 1px solid #f1f5f9; background: #fafafa;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="
                        width: 36px; height: 36px;
                        border-radius: 50%;
                        background: linear-gradient(135deg, #0ea5e9, #6366f1);
                        color: white;
                        display: flex; align-items: center; justify-content: center;
                        font-weight: 700; font-size: 13px;
                        flex-shrink: 0;
                    ">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div style="min-width: 0; flex: 1;">
                    <div style="font-size: 12px; font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ auth()->user()->name ?? 'User' }}
                    </div>
                    <div style="font-size: 10px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ auth()->user()->email ?? '' }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>