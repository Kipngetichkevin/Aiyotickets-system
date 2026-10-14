{{-- sidebar.blade.php --}}
@php
    // Role-based menu permissions, resolved on the server so links a role
    // can't use are never sent to the browser.
    //  guest    → public browsing only
    //  user     → their own account (tickets, QRs, orders)
    //  merchant → manage events, experiences, attendees
    //  admin    → everything + users + system
    $menuPermissions = [
        'guest'    => ['browse-events', 'browse-experiences'],
        'user'     => ['dashboard', 'browse-events', 'browse-experiences', 'my-tickets', 'my-qrs', 'my-instalments', 'orders'],
        // Not listed until their pages exist: 'tickets', 'attendees', 'users', 'budgets'
        // (tickets and attendees are reached from each event's page)
        'merchant' => ['dashboard', 'events', 'experiences', 'bookings', 'browse-events', 'browse-experiences', 'my-instalments', 'orders', 'qr-codes', 'promo-codes', 'instalments', 'reports', 'payouts'],
        'admin'    => ['dashboard', 'events', 'experiences', 'bookings', 'browse-events', 'browse-experiences', 'my-instalments', 'orders', 'qr-codes', 'promo-codes', 'instalments', 'reports', 'payouts', 'payout-requests', 'organisers', 'payments', 'system'],
    ];

    $menuUser = auth()->user();
    $menuRole = match (true) {
        ! $menuUser                    => 'guest',
        $menuUser->hasRole('admin')    => 'admin',
        $menuUser->hasRole('merchant') => 'merchant',
        default                        => 'user',
    };
    $menuAllowed = $menuPermissions[$menuRole];
@endphp

<aside
  :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
  class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 duration-300 ease-linear dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0"
  @click.outside="sidebarToggle = false"
>
<!-- SIDEBAR HEADER -->
<div
  :class="sidebarToggle ? 'justify-center' : 'justify-between'"
  class="sidebar-header flex items-center gap-2 pb-7 pt-8"
>
  <a href="{{ auth()->check() ? route('dashboard') : route('home') }}">
    <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
      <img class="dark:hidden w-20 h-auto" src="{{ asset('images/logo/logo.svg') }}" alt="Aiyo Tickets" />
      <img
        class="hidden dark:block w-20 h-auto"
        src="{{ asset('images/logo/logo-dark.svg') }}"
        alt="Aiyo Tickets"
      />
    </span>

    <img
      class="logo-icon"
      :class="sidebarToggle ? 'lg:block' : 'hidden'"
      src="{{ asset('images/logo/logo-icon.svg') }}"
      alt="Aiyo Tickets"
      class="w-10 h-auto"
    />
  </a>
</div>
<!-- SIDEBAR HEADER -->

  <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">
    <!-- Sidebar Menu -->
    <nav x-data="{
        selected: $persist('{{ request()->routeIs('dashboard') ? 'Dashboard' : '' }}')
    }">
      <!-- Menu Group -->
      <div>
        <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
          <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">MENU</span>

          <svg
            :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
            class="menu-group-icon mx-auto fill-current"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
              fill=""
            />
          </svg>
        </h3>

        <ul class="mb-6 flex flex-col gap-4">
          <!-- Dashboard - Available to all roles -->
          @if (in_array('dashboard', $menuAllowed))
          <li>
            <a
              href="{{ route('dashboard') }}"
              @click="selected = (selected === 'Dashboard' ? '':'Dashboard')"
              class="menu-item group"
              :class="(selected === 'Dashboard') || {{ request()->routeIs('dashboard') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Dashboard') || {{ request()->routeIs('dashboard') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z"
                  fill=""
                />
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Dashboard</span>
            </a>
          </li>
          @endif

          <!-- Browse Events (guests + all logged-in users) -->
          @if (in_array('browse-events', $menuAllowed))
          <li>
            <a
              href="{{ route('events.browse') }}"
              @click="selected = (selected === 'Browse Events' ? '' : 'Browse Events')"
              class="menu-item group"
              :class="(selected === 'Browse Events') || {{ request()->routeIs('events.browse') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Browse Events') || {{ request()->routeIs('events.browse') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2" fill="none"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65" stroke="currentColor" stroke-width="2"/>
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Browse Events</span>
            </a>
          </li>
          @endif

          <!-- Browse Experiences (guests + all logged-in users) -->
          @if (in_array('browse-experiences', $menuAllowed))
          <li>
            <a
              href="{{ route('experiences.browse') }}"
              @click="selected = (selected === 'Browse Experiences' ? '' : 'Browse Experiences')"
              class="menu-item group"
              :class="(selected === 'Browse Experiences') || {{ request()->routeIs('experiences.browse') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Browse Experiences') || {{ request()->routeIs('experiences.browse') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423z"
                  fill=""
                />
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Browse Experiences</span>
            </a>
          </li>
          @endif

          <!-- Events (merchants & admins only) -->
          @if (in_array('events', $menuAllowed))
          <li>
            <a
              href="{{ route('events.index') }}"
              @click="selected = (selected === 'Events' ? '' : 'Events')"
              class="menu-item group"
              :class="(selected === 'Events') || {{ request()->routeIs('events.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Events') || {{ request()->routeIs('events.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Events</span>
            </a>
          </li>
          @endif

          <!-- Experiences (merchant/admin) -->
          @if (in_array('experiences', $menuAllowed))
          <li>
            <a
              href="{{ route('experiences.index') }}"
              @click="selected = (selected === 'Experiences' ? '' : 'Experiences')"
              class="menu-item group"
              :class="(selected === 'Experiences') || {{ request()->routeIs('experiences.index') || request()->routeIs('experiences.create') || request()->routeIs('experiences.edit') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Experiences') || {{ request()->routeIs('experiences.index') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423z"
                  fill=""
                />
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Experiences</span>
            </a>
          </li>
          @endif

          <!-- Bookings (merchants & admins only) -->
          @if (in_array('bookings', $menuAllowed))
          <li>
            <a
              href="{{ route('bookings.index') }}"
              @click="selected = (selected === 'Bookings' ? '' : 'Bookings')"
              class="menu-item group"
              :class="(selected === 'Bookings') || {{ request()->routeIs('bookings.index') || request()->routeIs('experiences.bookings') || request()->routeIs('qrcode.experience-scanner') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Bookings') || {{ request()->routeIs('bookings.index') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M6.75 2.25A.75.75 0 017.5 3v1.5h9V3A.75.75 0 0118 3v1.5h.75a3 3 0 013 3v11.25a3 3 0 01-3 3H5.25a3 3 0 01-3-3V7.5a3 3 0 013-3H6V3a.75.75 0 01.75-.75zm13.5 9a1.5 1.5 0 00-1.5-1.5H5.25a1.5 1.5 0 00-1.5 1.5v7.5a1.5 1.5 0 001.5 1.5h13.5a1.5 1.5 0 001.5-1.5v-7.5z"
                  fill=""
                />
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Bookings</span>
            </a>
          </li>
          @endif

          <!-- My Tickets (Users only) -->
          @if (in_array('my-tickets', $menuAllowed))
          <li>
            <a
              href="{{ route('my-qrs') }}"
              @click="selected = (selected === 'My Tickets' ? '' : 'My Tickets')"
              class="menu-item group"
              :class="(selected === 'My Tickets') ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'My Tickets') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M2 6C2 4.89543 2.89543 4 4 4H20C21.1046 4 22 4.89543 22 6V18C22 19.1046 21.1046 20 20 20H4C2.89543 20 2 19.1046 2 18V6ZM4 6H20V18H4V6ZM7 10H17V12H7V10Z"
                  fill=""
                />
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">My Tickets</span>
            </a>
          </li>
          @endif

          <!-- My QR Codes (Users only) -->
          @if (in_array('my-qrs', $menuAllowed))
          <li>
            <a
              href="{{ route('my-qrs') }}"
              @click="selected = (selected === 'My QR Codes' ? '' : 'My QR Codes')"
              class="menu-item group"
              :class="(selected === 'My QR Codes') || {{ request()->routeIs('my-qrs') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'My QR Codes') || {{ request()->routeIs('my-qrs') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"
                  fill=""
                />
                <path
                  d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"
                  fill=""
                />
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">My QR Codes</span>
            </a>
          </li>
          @endif

          @if (in_array('my-instalments', $menuAllowed))
          <li>
            <a
              href="{{ route('instalments.mine') }}"
              @click="selected = (selected === 'My instalments' ? '' : 'My instalments')"
              class="menu-item group"
              :class="(selected === 'My instalments') || {{ request()->routeIs('instalments.mine') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'My instalments') || {{ request()->routeIs('instalments.mine') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
              >
                <path d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">My instalments</span>
            </a>
          </li>
          @endif


          <!-- Tickets (Merchants & Admin) -->
          @if (in_array('tickets', $menuAllowed))
          <li>
            <a
              href="#"
              @click.prevent="selected = (selected === 'Tickets' ? '' : 'Tickets')"
              class="menu-item group"
              :class="(selected === 'Tickets') || {{ request()->routeIs('tickets.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Tickets') || {{ request()->routeIs('tickets.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M2 6C2 4.89543 2.89543 4 4 4H20C21.1046 4 22 4.89543 22 6V18C22 19.1046 21.1046 20 20 20H4C2.89543 20 2 19.1046 2 18V6ZM4 6H20V18H4V6ZM7 10H17V12H7V10Z"
                  fill=""
                />
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Tickets</span>
            </a>
          </li>
          @endif

          <!-- Orders -->
          @if (in_array('orders', $menuAllowed))
          <li>
            <a
              href="{{ route('cart.view') }}"
              @click="selected = (selected === 'Orders' ? '' : 'Orders')"
              class="menu-item group"
              :class="(selected === 'Orders') || {{ request()->routeIs('cart.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Orders') || {{ request()->routeIs('cart.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M2 3C2 2.44772 2.44772 2 3 2H6.3826C6.87021 2 7.30448 2.31931 7.44716 2.77784L8.5 6.5H20C20.5128 6.5 20.9355 6.8713 20.9931 7.38002L21.7931 14.38C21.8644 15.0069 21.3833 15.56 20.7564 15.56H7.7465C7.29379 15.56 6.89485 15.2586 6.76634 14.8301L4.85336 8.43678L4.11685 5.56H3C2.44772 5.56 2 5.11228 2 4.56V3ZM6.96539 8.06L8.2477 12.06H19.6886L19.0886 7.06H7.07398H6.96539ZM7.10621 17.56C5.7882 17.56 4.72621 18.622 4.72621 19.94C4.72621 21.258 5.7882 22.32 7.10621 22.32C8.42421 22.32 9.48621 21.258 9.48621 19.94C9.48621 18.622 8.42421 17.56 7.10621 17.56ZM6.22621 19.94C6.22621 19.454 6.62021 19.06 7.10621 19.06C7.59221 19.06 7.98621 19.454 7.98621 19.94C7.98621 20.426 7.59221 20.82 7.10621 20.82C6.62021 20.82 6.22621 20.426 6.22621 19.94ZM18.5938 17.56C17.2758 17.56 16.2138 18.622 16.2138 19.94C16.2138 21.258 17.2758 22.32 18.5938 22.32C19.9118 22.32 20.9738 21.258 20.9738 19.94C20.9738 18.622 19.9118 17.56 18.5938 17.56ZM17.7138 19.94C17.7138 19.454 18.1078 19.06 18.5938 19.06C19.0798 19.06 19.4738 19.454 19.4738 19.94C19.4738 20.426 19.0798 20.82 18.5938 20.82C18.1078 20.82 17.7138 20.426 17.7138 19.94Z"
                  fill=""
                />
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Cart</span>
            </a>
          </li>
          @endif

          <!-- Attendees -->
          @if (in_array('attendees', $menuAllowed))
          <li>
            <a
              href="#"
              @click.prevent="selected = (selected === 'Attendees' ? '' : 'Attendees')"
              class="menu-item group"
              :class="(selected === 'Attendees') || {{ request()->routeIs('attendees.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Attendees') || {{ request()->routeIs('attendees.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM17.0246 18.8566V18.8455C17.0246 16.7744 15.3457 15.0955 13.2746 15.0955H10.7246C8.65354 15.0955 6.97461 16.7744 6.97461 18.8455V18.856C8.38223 19.8895 10.1198 20.5 12 20.5C13.8798 20.5 15.6171 19.8898 17.0246 18.8566ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9991 7.25C10.8847 7.25 9.98126 8.15342 9.98126 9.26784C9.98126 10.3823 10.8847 11.2857 11.9991 11.2857C13.1135 11.2857 14.0169 10.3823 14.0169 9.26784C14.0169 8.15342 13.1135 7.25 11.9991 7.25ZM8.48126 9.26784C8.48126 7.32499 10.0563 5.75 11.9991 5.75C13.9419 5.75 15.5169 7.32499 15.5169 9.26784C15.5169 11.2107 13.9419 12.7857 11.9991 12.7857C10.0563 12.7857 8.48126 11.2107 8.48126 9.26784Z"
                  fill=""
                />
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Attendees</span>
            </a>
          </li>
          @endif

          <!-- QR Codes (Merchants & Admin — scanner) -->
          @if (in_array('qr-codes', $menuAllowed))
          <li>
            <a
              href="{{ route('qrcode.index') }}"
              @click="selected = (selected === 'QR Codes' ? '' : 'QR Codes')"
              class="menu-item group"
              :class="(selected === 'QR Codes') || {{ request()->routeIs('qrcode.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'QR Codes') || {{ request()->routeIs('qrcode.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M2 3C2 2.44772 2.44772 2 3 2H8C8.55228 2 9 2.44772 9 3V8C9 8.55228 8.55228 9 8 9H3C2.44772 9 2 8.55228 2 8V3ZM4 4V7H7V4H4ZM2 15C2 14.4477 2.44772 14 3 14H8C8.55228 14 9 14.4477 9 15V20C9 20.5523 8.55228 21 8 21H3C2.44772 21 2 20.5523 2 20V15ZM4 16V19H7V16H4ZM15 2C14.4477 2 14 2.44772 14 3V8C14 8.55228 14.4477 9 15 9H20C20.5523 9 21 8.55228 21 8V3C21 2.44772 20.5523 2 20 2H15ZM16 4V7H19V4H16ZM15 15C14.4477 15 14 15.4477 14 16V20C14 20.5523 14.4477 21 15 21H20C20.5523 21 21 20.5523 21 20V16C21 15.4477 20.5523 15 20 15H15ZM16 17V19H19V17H16ZM10 11C10 10.4477 10.4477 10 11 10H13C13.5523 10 14 10.4477 14 11V13C14 13.5523 13.5523 14 13 14H11C10.4477 14 10 13.5523 10 13V11Z"
                  fill=""
                />
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">QR Codes</span>
            </a>
          </li>
          @endif

          <!-- Reports -->
          @if (in_array('promo-codes', $menuAllowed))
          <li>
            <a
              href="{{ route('promo-codes.index') }}"
              @click="selected = (selected === 'Promo codes' ? '' : 'Promo codes')"
              class="menu-item group"
              :class="(selected === 'Promo codes') || {{ request()->routeIs('promo-codes.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Promo codes') || {{ request()->routeIs('promo-codes.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
              >
                <path d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3zM6 6h.008v.008H6V6z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Promo codes</span>
            </a>
          </li>
          @endif

          @if (in_array('instalments', $menuAllowed))
          <li>
            <a
              href="{{ route('instalments.settings') }}"
              @click="selected = (selected === 'Lipa mdogo mdogo' ? '' : 'Lipa mdogo mdogo')"
              class="menu-item group"
              :class="(selected === 'Lipa mdogo mdogo') || {{ request()->routeIs('instalments.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Lipa mdogo mdogo') || {{ request()->routeIs('instalments.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
              >
                <path d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Lipa mdogo mdogo</span>
            </a>
          </li>
          @endif

          @if (in_array('reports', $menuAllowed))
          <li>
            <a
              href="{{ route('reports') }}"
              @click="selected = (selected === 'Reports' ? '' : 'Reports')"
              class="menu-item group"
              :class="(selected === 'Reports') || {{ request()->routeIs('reports') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Reports') || {{ request()->routeIs('reports') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M12 2C11.5858 2 11.25 2.33579 11.25 2.75V12C11.25 12.4142 11.5858 12.75 12 12.75H21.25C21.6642 12.75 22 12.4142 22 12C22 6.47715 17.5228 2 12 2ZM12.75 11.25V3.53263C13.2645 3.57761 13.7659 3.66843 14.25 3.80098V3.80099C15.6929 4.19606 16.9827 4.96184 18.0104 5.98959C19.0382 7.01734 19.8039 8.30707 20.199 9.75C20.3316 10.2341 20.4224 10.7355 20.4674 11.25H12.75ZM2 12C2 7.25083 5.31065 3.27489 9.75 2.25415V3.80099C6.14748 4.78734 3.5 8.0845 3.5 12C3.5 16.6944 7.30558 20.5 12 20.5C15.9155 20.5 19.2127 17.8525 20.199 14.25H21.7459C20.7251 18.6894 16.7492 22 12 22C6.47715 22 2 17.5229 2 12Z"
                  fill=""
                />
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Reports</span>
            </a>
          </li>
          @endif

          @if (in_array('payouts', $menuAllowed))
          <li>
            <a
              href="{{ route('payouts.index') }}"
              @click="selected = (selected === 'Payouts' ? '' : 'Payouts')"
              class="menu-item group"
              :class="(selected === 'Payouts') || {{ request()->routeIs('payouts.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Payouts') || {{ request()->routeIs('payouts.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
              >
                <path d="M3.75 7.5h16.5v11.25H3.75V7.5zm0 0V6a1.5 1.5 0 011.5-1.5h12a1.5 1.5 0 011.5 1.5v1.5M15.75 13.125h1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Payouts</span>
            </a>
          </li>
          @endif

          @if (in_array('payout-requests', $menuAllowed))
          <li>
            <a
              href="{{ route('admin.payouts.index') }}"
              @click="selected = (selected === 'Payout requests' ? '' : 'Payout requests')"
              class="menu-item group"
              :class="(selected === 'Payout requests') || {{ request()->routeIs('admin.payouts.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Payout requests') || {{ request()->routeIs('admin.payouts.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
              >
                <path d="M4.5 12.75l2.25-7.5h10.5l2.25 7.5M4.5 12.75v5.25h15v-5.25M4.5 12.75h4.5l1.5 2.25h3l1.5-2.25h4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Payout requests</span>
            </a>
          </li>
          @endif

          @if (in_array('organisers', $menuAllowed))
          <li>
            <a
              href="{{ route('admin.organisers.index') }}"
              @click="selected = (selected === 'Organisers' ? '' : 'Organisers')"
              class="menu-item group"
              :class="(selected === 'Organisers') || {{ request()->routeIs('admin.organisers.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Organisers') || {{ request()->routeIs('admin.organisers.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
              >
                <path d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198v.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Organisers</span>
              @if ($pendingOrganisers = \App\Models\Merchant::where('status', 'pending')->count())
                <span class="ml-auto rounded-full bg-amber-500 px-2 py-0.5 text-xs font-bold text-white" :class="sidebarToggle ? 'lg:hidden' : ''">{{ $pendingOrganisers }}</span>
              @endif
            </a>
          </li>
          @endif

          @if (in_array('payments', $menuAllowed))
          <li>
            <a
              href="{{ route('admin.payments.index') }}"
              @click="selected = (selected === 'M-Pesa payments' ? '' : 'M-Pesa payments')"
              class="menu-item group"
              :class="(selected === 'M-Pesa payments') || {{ request()->routeIs('admin.payments.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'M-Pesa payments') || {{ request()->routeIs('admin.payments.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
              >
                <path d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">M-Pesa payments</span>
            </a>
          </li>
          @endif

          <!-- Budgets -->

          <!-- Users (Admin only) -->
          @if (in_array('users', $menuAllowed))
          <li>
            <a
              href="#"
              @click.prevent="selected = (selected === 'Users' ? '' : 'Users')"
              class="menu-item group"
              :class="(selected === 'Users') ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'Users') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM17.0246 18.8566V18.8455C17.0246 16.7744 15.3457 15.0955 13.2746 15.0955H10.7246C8.65354 15.0955 6.97461 16.7744 6.97461 18.8455V18.856C8.38223 19.8895 10.1198 20.5 12 20.5C13.8798 20.5 15.6171 19.8898 17.0246 18.8566ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9991 7.25C10.8847 7.25 9.98126 8.15342 9.98126 9.26784C9.98126 10.3823 10.8847 11.2857 11.9991 11.2857C13.1135 11.2857 14.0169 10.3823 14.0169 9.26784C14.0169 8.15342 13.1135 7.25 11.9991 7.25ZM8.48126 9.26784C8.48126 7.32499 10.0563 5.75 11.9991 5.75C13.9419 5.75 15.5169 7.32499 15.5169 9.26784C15.5169 11.2107 13.9419 12.7857 11.9991 12.7857C10.0563 12.7857 8.48126 11.2107 8.48126 9.26784Z"
                  fill=""
                />
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Users</span>
            </a>
          </li>
          @endif

          <!-- System Settings (Admin only) -->
          @if (in_array('system', $menuAllowed))
          <li>
            <a
              href="{{ route('system.index') }}"
              @click="selected = (selected === 'System' ? '' : 'System')"
              class="menu-item group"
              :class="(selected === 'System') || {{ request()->routeIs('system.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg
                :class="(selected === 'System') || {{ request()->routeIs('system.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M12 2C11.5858 2 11.25 2.33579 11.25 2.75V5.24661C9.8027 5.59584 8.55044 6.37814 7.57556 7.42526L5.47022 5.32847L5.46997 5.32872C5.17708 5.03583 4.70221 5.03583 4.40932 5.32872L4.05575 5.68229C3.76286 5.97518 3.76286 6.45006 4.05575 6.74295L6.17363 8.85229C5.13226 9.82459 4.35679 11.0757 4.01247 12.5H1.75C1.33579 12.5 1 12.8358 1 13.25V13.75C1 14.1642 1.33579 14.5 1.75 14.5H4.01247C4.35679 15.9243 5.13226 17.1754 6.17363 18.1477L4.05575 20.2571C3.76286 20.55 3.76286 21.0249 4.05575 21.3178L4.40932 21.6714C4.70221 21.9643 5.17709 21.9643 5.46998 21.6714L5.47022 21.6712L7.57556 19.5748C8.55044 20.6219 9.8027 21.4042 11.25 21.7534V24.25C11.25 24.6642 11.5858 25 12 25H12.5C12.9142 25 13.25 24.6642 13.25 24.25V21.7534C14.6973 21.4042 15.9496 20.6219 16.9244 19.5748L19.0298 21.6712L19.03 21.6713C19.3229 21.9642 19.7978 21.9642 20.0907 21.6713L20.4442 21.3178C20.7371 21.0249 20.7371 20.55 20.4442 20.2571L18.3264 18.1477C19.3677 17.1754 20.1432 15.9243 20.4875 14.5H22.75C23.1642 14.5 23.5 14.1642 23.5 13.75V13.25C23.5 12.8358 23.1642 12.5 22.75 12.5H20.4875C20.1432 11.0757 19.3677 9.82459 18.3264 8.85229L20.4442 6.74295C20.7371 6.45006 20.7371 5.97518 20.4442 5.68229L20.0907 5.32872C19.7978 5.03583 19.3229 5.03583 19.03 5.32872L16.9244 7.42526C15.9496 6.37814 14.6973 5.59584 13.25 5.24661V2.75C13.25 2.33579 12.9142 2 12.5 2H12ZM14.25 14C14.25 12.7574 13.2426 11.75 12 11.75C10.7574 11.75 9.75 12.7574 9.75 14C9.75 15.2426 10.7574 16.25 12 16.25C13.2426 16.25 14.25 15.2426 14.25 14Z"
                  fill=""
                />
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">System</span>
            </a>
          </li>
          @endif
        </ul>
      </div>
    </nav>
    <!-- Sidebar Menu -->
  </div>
</aside>