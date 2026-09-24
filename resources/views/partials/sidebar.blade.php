{{-- sidebar.blade.php --}}
@auth
<script>
window.menuPermissions = {
    'user':     ['dashboard', 'browse', 'my-tickets', 'my-orders'],
    'merchant': ['dashboard', 'events', 'tickets', 'orders', 'attendees', 'qr-codes', 'reports', 'budgets', 'finance'],
    'admin':    ['dashboard', 'events', 'tickets', 'orders', 'attendees', 'users', 'qr-codes', 'reports', 'budgets', 'finance', 'system', 'mpesa-callbacks'],
};
</script>

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
  <a href="{{ route('dashboard') }}">
    <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
      <img class="dark:hidden w-20 h-auto" src="{{ asset('images/logo/logo.svg') }}" alt="Aiyo Tickets" />
      <img class="hidden dark:block w-20 h-auto" src="{{ asset('images/logo/logo-dark.svg') }}" alt="Aiyo Tickets" />
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
    <nav x-data="{
        selected: $persist('{{ request()->routeIs('dashboard') ? 'Dashboard' : '' }}'),
        userRole: '{{ auth()->check() ? (auth()->user()->getRoleNames()->first() ?? 'user') : 'guest' }}',
        hasPermission(menu) {
            const permissions = window.menuPermissions[this.userRole] || [];
            return permissions.includes(menu);
        }
    }">
      <div>
        <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
          <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">MENU</span>
        </h3>

        <ul class="mb-6 flex flex-col gap-4">
          <!-- Dashboard -->
          <li x-show="hasPermission('dashboard')">
            <a
              href="{{ route('dashboard') }}"
              @click="selected = (selected === 'Dashboard' ? '':'Dashboard')"
              class="menu-item group"
              :class="(selected === 'Dashboard') || {{ request()->routeIs('dashboard') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Dashboard') || {{ request()->routeIs('dashboard') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5Z" fill=""/>
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Dashboard</span>
            </a>
          </li>

          <!-- Browse Events -->
          <li x-show="hasPermission('browse')">
            <a
              href="{{ route('browse') }}"
              @click="selected = (selected === 'Browse Events' ? '' : 'Browse Events')"
              class="menu-item group"
              :class="(selected === 'Browse Events') || {{ request()->routeIs('browse') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Browse Events') || {{ request()->routeIs('browse') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Browse Events</span>
            </a>
          </li>

          <!-- My Tickets -->
          <li x-show="hasPermission('my-tickets')">
            <a
              href="{{ route('my-tickets.index') }}"
              @click="selected = (selected === 'My Tickets' ? '' : 'My Tickets')"
              class="menu-item group"
              :class="(selected === 'My Tickets') || {{ request()->routeIs('my-tickets.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'My Tickets') || {{ request()->routeIs('my-tickets.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">My Tickets</span>
            </a>
          </li>

          <!-- My Orders -->
          <li x-show="hasPermission('my-orders')">
            <a
              href="{{ route('my-orders.index') }}"
              @click="selected = (selected === 'My Orders' ? '' : 'My Orders')"
              class="menu-item group"
              :class="(selected === 'My Orders') || {{ request()->routeIs('my-orders.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'My Orders') || {{ request()->routeIs('my-orders.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">My Orders</span>
            </a>
          </li>

          <!-- Events -->
          <li x-show="hasPermission('events')">
            <a
              href="{{ route('events.index') }}"
              @click="selected = (selected === 'Events' ? '' : 'Events')"
              class="menu-item group"
              :class="(selected === 'Events') || {{ request()->routeIs('events.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Events') || {{ request()->routeIs('events.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Events</span>
            </a>
          </li>

          <!-- Tickets -->
          <li x-show="hasPermission('tickets')">
            <a
              href="{{ route('tickets.all') }}"
              @click="selected = (selected === 'Tickets' ? '' : 'Tickets')"
              class="menu-item group"
              :class="(selected === 'Tickets') || {{ request()->routeIs('tickets.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Tickets') || {{ request()->routeIs('tickets.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M2 6C2 4.89543 2.89543 4 4 4H20C21.1046 4 22 4.89543 22 6V18C22 19.1046 21.1046 20 20 20H4C2.89543 20 2 19.1046 2 18V6ZM4 6H20V18H4V6ZM7 10H17V12H7V10Z" fill=""/>
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Tickets</span>
            </a>
          </li>

          <!-- Orders -->
          <li x-show="hasPermission('orders')">
            <a
              href="{{ route('orders.index') }}"
              @click="selected = (selected === 'Orders' ? '' : 'Orders')"
              class="menu-item group"
              :class="(selected === 'Orders') || {{ request()->routeIs('orders.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Orders') || {{ request()->routeIs('orders.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M2 3C2 2.44772 2.44772 2 3 2H6.3826C6.87021 2 7.30448 2.31931 7.44716 2.77784L8.5 6.5H20C20.5128 6.5 20.9355 6.8713 20.9931 7.38002L21.7931 14.38C21.8644 15.0069 21.3833 15.56 20.7564 15.56H7.7465C7.29379 15.56 6.89485 15.2586 6.76634 14.8301L4.85336 8.43678L4.11685 5.56H3C2.44772 5.56 2 5.11228 2 4.56V3Z" fill=""/>
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Orders</span>
            </a>
          </li>

          <!-- Attendees -->
          <li x-show="hasPermission('attendees')">
            <a
              href="{{ route('attendees.all') }}"
              @click="selected = (selected === 'Attendees' ? '' : 'Attendees')"
              class="menu-item group"
              :class="(selected === 'Attendees') || {{ request()->routeIs('attendees.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Attendees') || {{ request()->routeIs('attendees.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12Z" fill=""/>
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Attendees</span>
            </a>
          </li>

          <!-- QR Codes -->
          <li x-show="hasPermission('qr-codes')">
            <a
              href="{{ route('qrcode.index') }}"
              @click="selected = (selected === 'QR Codes' ? '' : 'QR Codes')"
              class="menu-item group"
              :class="(selected === 'QR Codes') || {{ request()->routeIs('qrcode.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'QR Codes') || {{ request()->routeIs('qrcode.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M2 3C2 2.44772 2.44772 2 3 2H8C8.55228 2 9 2.44772 9 3V8C9 8.55228 8.55228 9 8 9H3C2.44772 9 2 8.55228 2 8V3ZM4 4V7H7V4H4ZM2 15C2 14.4477 2.44772 14 3 14H8C8.55228 14 9 14.4477 9 15V20C9 20.5523 8.55228 21 8 21H3C2.44772 21 2 20.5523 2 20V15Z" fill=""/>
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">QR Codes</span>
            </a>
          </li>

          <!-- Reports -->
          <li x-show="hasPermission('reports')">
            <a
              href="{{ route('reports') }}"
              @click="selected = (selected === 'Reports' ? '' : 'Reports')"
              class="menu-item group"
              :class="(selected === 'Reports') || {{ request()->routeIs('reports') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Reports') || {{ request()->routeIs('reports') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C11.5858 2 11.25 2.33579 11.25 2.75V12C11.25 12.4142 11.5858 12.75 12 12.75H21.25C21.6642 12.75 22 12.4142 22 12C22 6.47715 17.5228 2 12 2Z" fill=""/>
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Reports</span>
            </a>
          </li>

          <!-- Budgets -->
          <li x-show="hasPermission('budgets')">
            <a
              href="{{ route('budgets.index') }}"
              @click="selected = (selected === 'Budgets' ? '' : 'Budgets')"
              class="menu-item group"
              :class="(selected === 'Budgets') || {{ request()->routeIs('budgets.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Budgets') || {{ request()->routeIs('budgets.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="20" height="14" x="2" y="7" rx="2" ry="2" />
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Budgets</span>
            </a>
          </li>

          <!-- Finance -->
          <li x-show="hasPermission('finance')">
            <a
              href="{{ route('finance.index') }}"
              @click="selected = (selected === 'Finance' ? '' : 'Finance')"
              class="menu-item group"
              :class="(selected === 'Finance') || {{ request()->routeIs('finance.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'Finance') || {{ request()->routeIs('finance.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23" />
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Finance</span>
            </a>
          </li>

          <!-- M-Pesa Callbacks (Admin) -->
          <li x-show="hasPermission('mpesa-callbacks')">
            <a
              href="{{ route('admin.mpesa-callbacks.index') }}"
              @click="selected = (selected === 'M-Pesa Callbacks' ? '' : 'M-Pesa Callbacks')"
              class="menu-item group"
              :class="(selected === 'M-Pesa Callbacks') || {{ request()->routeIs('admin.mpesa-callbacks.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'M-Pesa Callbacks') || {{ request()->routeIs('admin.mpesa-callbacks.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">M-Pesa Callbacks</span>
            </a>
          </li>

          <!-- System (Admin only) -->
          <li x-show="hasPermission('system')">
            <a
              href="{{ route('system.index') }}"
              @click="selected = (selected === 'System' ? '' : 'System')"
              class="menu-item group"
              :class="(selected === 'System') || {{ request()->routeIs('system.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'"
            >
              <svg :class="(selected === 'System') || {{ request()->routeIs('system.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C11.5858 2 11.25 2.33579 11.25 2.75V5.24661C9.8027 5.59584 8.55044 6.37814 7.57556 7.42526L5.47022 5.32847C5.17733 5.03558 4.70246 5.03558 4.40956 5.32847L4.05599 5.68204C3.76309 5.97493 3.76309 6.4498 4.05599 6.7427L6.17387 8.85204C5.1325 9.82434 4.35702 11.0755 4.01271 12.5H1.75C1.33579 12.5 1 12.8358 1 13.25V13.75C1 14.1642 1.33579 14.5 1.75 14.5H4.01271C4.35702 15.9245 5.1325 17.1756 6.17387 18.1479L4.05599 20.2573C3.76309 20.5502 3.76309 21.0251 4.05599 21.318L4.40956 21.6715C4.70246 21.9644 5.17733 21.9644 5.47022 21.6715L7.57556 19.5747C8.55044 20.6219 9.8027 21.4041 11.25 21.7534V24.25C11.25 24.6642 11.5858 25 12 25H12.5C12.9142 25 13.25 24.6642 13.25 24.25V21.7534C14.6973 21.4041 15.9496 20.6219 16.9244 19.5747L19.0298 21.6715C19.3227 21.9644 19.7975 21.9644 20.0904 21.6715L20.444 21.318C20.7369 21.0251 20.7369 20.5502 20.444 20.2573L18.3261 18.1479C19.3675 17.1756 20.143 15.9245 20.4873 14.5H22.75C23.1642 14.5 23.5 14.1642 23.5 13.75V13.25C23.5 12.8358 23.1642 12.5 22.75 12.5H20.4873C20.143 11.0755 19.3675 9.82434 18.3261 8.85204L20.444 6.7427C20.7369 6.4498 20.7369 5.97493 20.444 5.68204L20.0904 5.32847C19.7975 5.03558 19.3227 5.03558 19.0298 5.32847L16.9244 7.42526C15.9496 6.37814 14.6973 5.59584 13.25 5.24661V2.75C13.25 2.33579 12.9142 2 12.5 2H12ZM14.25 14C14.25 12.7574 13.2426 11.75 12 11.75C10.7574 11.75 9.75 12.7574 9.75 14C9.75 15.2426 10.7574 16.25 12 16.25C13.2426 16.25 14.25 15.2426 14.25 14Z" fill=""/>
              </svg>
              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">System</span>
            </a>
          </li>
        </ul>
      </div>
    </nav>
  </div>
</aside>
@endauth