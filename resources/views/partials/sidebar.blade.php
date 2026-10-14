{{-- sidebar.blade.php — back-office menu, grouped by what people do. Built on the server
     per role, so links a role can't use are never sent to the browser. --}}
@php
    $menuUser = auth()->user();
    $role = match (true) {
        ! $menuUser                    => 'guest',
        $menuUser->hasRole('admin')    => 'admin',
        $menuUser->hasRole('merchant') => 'merchant',
        default                        => 'user',
    };

    // Outline icons (24×24, stroke)
    $icons = [
        'dashboard' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
        'event'     => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
        'map'       => 'M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z',
        'bookings'  => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z',
        'qr'        => 'M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM13.5 14.25h.75v.75h-.75v-.75zM19.5 14.25h.75v.75h-.75v-.75zM16.5 17.25h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75z',
        'tag'       => 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3zM6 6h.008v.008H6V6z',
        'coins'     => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'chart'     => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
        'wallet'    => 'M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3',
        'bank'      => 'M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z',
        'users'     => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198v.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z',
        'cog'       => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
        'ticket'    => 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z',
        'search'    => 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z',
        'globe'     => 'M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418',
    ];

    // [label, route, active route patterns, icon]; a 'children' key makes a collapsible group
    $selling = [
        ['Events', 'events.index', ['events.*', 'tickets.*'], 'event'],
        ['Experiences', 'experiences.index', ['experiences.index', 'experiences.json'], 'map'],
        ['Bookings', 'bookings.index', ['bookings.index', 'experiences.bookings*'], 'bookings'],
        ['Check-in scanner', 'qrcode.index', ['qrcode.*'], 'qr'],
        ['Promo codes', 'promo-codes.index', ['promo-codes.*'], 'tag'],
        ['Lipa mdogo mdogo', 'instalments.settings', ['instalments.settings', 'instalments.update'], 'coins'],
        ['Reports', 'reports', ['reports*'], 'chart'],
    ];

    $pendingOrganisers = $role === 'admin' ? \App\Models\Merchant::where('status', 'pending')->count() : 0;
    $billsToApprove    = $role === 'admin' ? \App\Models\Bill::where('status', 'awaiting_approval')->count() : 0;

    $sections = match ($role) {
        'admin' => [
            [null, [['Dashboard', 'dashboard', ['dashboard'], 'dashboard']]],
            ['Selling', $selling],
            ['Money', [
                ['Finance', null, ['finance.*', 'admin.payments.*', 'admin.payouts.*', 'payouts.*'], 'bank', 'badge' => $billsToApprove, 'children' => [
                    ['Overview', 'finance.overview', ['finance.overview']],
                    ['Collections (M-Pesa)', 'admin.payments.index', ['admin.payments.*']],
                    ['Organiser payouts', 'admin.payouts.index', ['admin.payouts.*']],
                    ['Bills', 'finance.bills.index', ['finance.bills.*'], 'badge' => $billsToApprove],
                    ['Payments made', 'finance.payments.index', ['finance.payments.*']],
                    ['Creditors', 'finance.creditors', ['finance.creditors']],
                    ['Suppliers & staff', 'finance.payees.index', ['finance.payees.*']],
                    ['Categories', 'finance.categories.index', ['finance.categories.*']],
                    ['My own payouts', 'payouts.index', ['payouts.*']],
                ]],
            ]],
            ['Admin', [
                ['Organisers', 'admin.organisers.index', ['admin.organisers.*'], 'users', 'badge' => $pendingOrganisers],
                ['System', 'system.index', ['system.*'], 'cog'],
                ['View site', 'home', [], 'globe'],
            ]],
        ],
        'merchant' => [
            [null, [['Dashboard', 'dashboard', ['dashboard'], 'dashboard']]],
            ['Selling', $selling],
            ['Money', [['Payouts', 'payouts.index', ['payouts.*'], 'wallet']]],
            [null, [['View site', 'home', [], 'globe']]],
        ],
        'user' => [
            ['My account', [
                ['My tickets', 'my-qrs', ['my-qrs*'], 'ticket'],
                ['My instalments', 'instalments.mine', ['instalments.mine'], 'coins'],
            ]],
            ['Discover', [
                ['Events', 'events.browse', ['events.browse'], 'search'],
                ['Experiences', 'experiences.browse', ['experiences.browse'], 'map'],
            ]],
        ],
        default => [
            ['Discover', [
                ['Events', 'events.browse', ['events.browse'], 'search'],
                ['Experiences', 'experiences.browse', ['experiences.browse'], 'map'],
            ]],
        ],
    };

    $isActive = fn (array $patterns) => $patterns && request()->routeIs(...$patterns);
@endphp

<aside
  :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
  class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 duration-300 ease-linear dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0"
  @click.outside="sidebarToggle = false"
>
  <div :class="sidebarToggle ? 'justify-center' : 'justify-between'" class="sidebar-header flex items-center gap-2 pb-7 pt-8">
    <a href="{{ auth()->check() ? route('dashboard') : route('home') }}">
      <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
        <img class="dark:hidden w-20 h-auto" src="{{ asset('images/logo/logo.svg') }}" alt="Aiyo Tickets" />
        <img class="hidden dark:block w-20 h-auto" src="{{ asset('images/logo/logo-dark.svg') }}" alt="Aiyo Tickets" />
      </span>
      <img class="logo-icon w-10 h-auto" :class="sidebarToggle ? 'lg:block' : 'hidden'" src="{{ asset('images/logo/logo-icon.svg') }}" alt="Aiyo Tickets" />
    </a>
  </div>

  <div class="no-scrollbar flex flex-col overflow-y-auto pb-8 duration-300 ease-linear">
    <nav>
      @foreach ($sections as [$title, $items])
        <div class="mb-5">
          @if ($title)
            <h3 class="mb-2 text-xs uppercase leading-[20px] tracking-wider text-gray-400">
              <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">{{ $title }}</span>
              <span class="hidden h-px w-6 bg-gray-200 dark:bg-gray-800" :class="sidebarToggle ? 'lg:mx-auto lg:block' : ''"></span>
            </h3>
          @endif

          <ul class="flex flex-col gap-1">
            @foreach ($items as $item)
              @php
                  [$label, $route, $patterns, $icon] = $item;
                  $active   = $isActive($patterns);
                  $children = $item['children'] ?? null;
                  $badge    = $item['badge'] ?? 0;
              @endphp
              <li @if ($children) x-data="{ open: {{ $active ? 'true' : 'false' }} }" @endif>
                <a href="{{ $children ? '#' : route($route) }}"
                   @if ($children) @click.prevent="open = !open" :aria-expanded="open" @endif
                   class="menu-item group {{ $active ? 'menu-item-active' : 'menu-item-inactive' }}">
                  <svg class="{{ $active ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="{{ $icons[$icon] }}"/>
                  </svg>
                  <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">{{ $label }}</span>
                  @if ($badge)
                    <span class="ml-auto rounded-full bg-amber-500 px-2 py-0.5 text-xs font-bold text-white" :class="sidebarToggle ? 'lg:hidden' : ''">{{ $badge }}</span>
                  @endif
                  @if ($children)
                    <svg class="menu-item-arrow {{ $badge ? 'ml-1' : '' }}" :class="[open ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '']"
                         width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                      <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  @endif
                </a>

                @if ($children)
                  <ul x-show="open" x-cloak :class="sidebarToggle ? 'lg:hidden' : ''" class="menu-dropdown mt-1 flex flex-col gap-1 pl-9">
                    @foreach ($children as $child)
                      @php($childActive = $isActive($child[2]))
                      <li>
                        <a href="{{ route($child[1]) }}" class="menu-dropdown-item group {{ $childActive ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                          {{ $child[0] }}
                          @if (!empty($child['badge']))
                            <span class="ml-auto rounded-full bg-amber-500 px-2 py-0.5 text-[11px] font-bold text-white">{{ $child['badge'] }}</span>
                          @endif
                        </a>
                      </li>
                    @endforeach
                  </ul>
                @endif
              </li>
            @endforeach
          </ul>
        </div>
      @endforeach
    </nav>
  </div>
</aside>
