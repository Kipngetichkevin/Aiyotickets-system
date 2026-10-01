{{-- sidebar.blade.php --}}
@auth
<script>
window.menuPermissions = {
    'user':     ['dashboard', 'browse', 'my-tickets', 'my-orders'],
    'merchant': ['dashboard', 'events', 'tickets', 'orders', 'attendees', 'qr-codes', 'reports', 'budgets', 'finance'],
    'admin':    ['dashboard', 'events', 'tickets', 'orders', 'attendees', 'users', 'qr-codes', 'reports', 'budgets', 'finance', 'system', 'mpesa-callbacks'],
};
</script>

<style>
  /* ---------- Sidebar nav item ---------- */
  .sb-item {
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.625rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    transition: background-color .18s ease, color .18s ease, transform .18s ease;
  }
  .dark .sb-item { color: #d1d5db; }

  .sb-item:hover {
    background-color: #f3f4f6;
    color: #111827;
  }
  .dark .sb-item:hover {
    background-color: rgba(255,255,255,0.05);
    color: #f3f4f6;
  }

  .sb-item.is-active {
    background-color: var(--primary-10, rgba(70,95,255,0.10));
    color: var(--primary-color, #465FFF);
  }
  .dark .sb-item.is-active {
    background-color: var(--primary-20, rgba(70,95,255,0.20));
    color: #9cb9ff;
  }

  /* Left accent bar for active item */
  .sb-item.is-active::before {
    content: "";
    position: absolute;
    left: -0.75rem;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 22px;
    background-color: var(--primary-color, #465FFF);
    border-radius: 0 4px 4px 0;
  }

  .sb-item > svg { width: 1.25rem; height: 1.25rem; flex-shrink: 0; }
  .sb-item > svg * { vector-effect: non-scaling-stroke; }

  /* ---------- Group label ---------- */
  .sb-group-label {
    padding: 0 0.75rem;
    margin-bottom: 0.375rem;
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #9ca3af;
  }
  .dark .sb-group-label { color: #6b7280; }

  /* Divider between groups */
  .sb-group { padding-bottom: 0.75rem; margin-bottom: 0.5rem; border-bottom: 1px solid #f3f4f6; }
  .sb-group:last-child { border-bottom: 0; padding-bottom: 0; margin-bottom: 0; }
  .dark .sb-group { border-color: rgba(255,255,255,0.05); }
</style>

<aside
  :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
  class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white duration-300 ease-linear dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0"
  @click.outside="if (window.innerWidth < 1024) sidebarToggle = false"
>
  {{-- ============ HEADER ============ --}}
  <div
    :class="sidebarToggle ? 'justify-center' : 'justify-between'"
    class="sidebar-header flex h-[72px] shrink-0 items-center border-b border-gray-100 px-5 dark:border-gray-800"
  >
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
      <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
        <img class="h-8 w-auto dark:hidden" src="{{ asset('images/logo/logo.svg') }}" alt="Aiyo Tickets" />
        <img class="hidden h-8 w-auto dark:block" src="{{ asset('images/logo/logo-dark.svg') }}" alt="Aiyo Tickets" />
      </span>
      <img
        class="logo-icon h-9 w-auto"
        :class="sidebarToggle ? 'lg:block' : 'hidden'"
        src="{{ asset('images/logo/logo-icon.svg') }}"
        alt="Aiyo Tickets"
      />
    </a>
  </div>

  {{-- ============ NAV ============ --}}
  <div class="no-scrollbar flex flex-1 flex-col overflow-y-auto px-3 py-4 duration-300 ease-linear">
    <nav x-data="{
        selected: $persist('{{ request()->routeIs('dashboard') ? 'Dashboard' : '' }}'),
        userRole: '{{ auth()->check() ? (auth()->user()->getRoleNames()->first() ?? 'user') : 'guest' }}',
        hasPermission(menu) {
            const permissions = window.menuPermissions[this.userRole] || [];
            return permissions.includes(menu);
        }
    }" class="flex flex-col gap-0.5">

      {{-- ========== MAIN ========== --}}
      <div class="sb-group">
        <p class="sb-group-label" :class="sidebarToggle ? 'lg:hidden' : ''">Main</p>

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           x-show="hasPermission('dashboard')"
           @click="selected = 'Dashboard'"
           class="sb-item"
           :class="(selected === 'Dashboard') || {{ request()->routeIs('dashboard') ? 'true' : 'false' }} ? 'is-active' : ''"
           :title="sidebarToggle ? 'Dashboard' : ''">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
          </svg>
          <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Dashboard</span>
        </a>

        {{-- Browse Events --}}
        <a href="{{ route('browse') }}"
           x-show="hasPermission('browse')"
           @click="selected = 'Browse Events'"
           class="sb-item"
           :class="(selected === 'Browse Events') || {{ request()->routeIs('browse') ? 'true' : 'false' }} ? 'is-active' : ''"
           :title="sidebarToggle ? 'Browse Events' : ''">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
          </svg>
          <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Browse Events</span>
        </a>
      </div>

      {{-- ========== MY ACCOUNT ========== --}}
      <div class="sb-group" x-show="hasPermission('my-tickets') || hasPermission('my-orders')">
        <p class="sb-group-label" :class="sidebarToggle ? 'lg:hidden' : ''">My Account</p>

        {{-- My Tickets --}}
        <a href="{{ route('my-tickets.index') }}"
           x-show="hasPermission('my-tickets')"
           @click="selected = 'My Tickets'"
           class="sb-item"
           :class="(selected === 'My Tickets') || {{ request()->routeIs('my-tickets.*') ? 'true' : 'false' }} ? 'is-active' : ''"
           :title="sidebarToggle ? 'My Tickets' : ''">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/>
          </svg>
          <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">My Tickets</span>
        </a>

        {{-- My Orders --}}
        <a href="{{ route('my-orders.index') }}"
           x-show="hasPermission('my-orders')"
           @click="selected = 'My Orders'"
           class="sb-item"
           :class="(selected === 'My Orders') || {{ request()->routeIs('my-orders.*') ? 'true' : 'false' }} ? 'is-active' : ''"
           :title="sidebarToggle ? 'My Orders' : ''">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
          </svg>
          <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">My Orders</span>
        </a>
      </div>

      {{-- ========== MANAGE ========== --}}
      <div class="sb-group" x-show="hasPermission('events') || hasPermission('tickets') || hasPermission('orders') || hasPermission('attendees') || hasPermission('qr-codes')">
        <p class="sb-group-label" :class="sidebarToggle ? 'lg:hidden' : ''">Manage</p>

        {{-- Events --}}
        <a href="{{ route('events.index') }}"
           x-show="hasPermission('events')"
           @click="selected = 'Events'"
           class="sb-item"
           :class="(selected === 'Events') || {{ request()->routeIs('events.*') ? 'true' : 'false' }} ? 'is-active' : ''"
           :title="sidebarToggle ? 'Events' : ''">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
          </svg>
          <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Events</span>
        </a>

        {{-- Tickets --}}
        <a href="{{ route('tickets.all') }}"
           x-show="hasPermission('tickets')"
           @click="selected = 'Tickets'"
           class="sb-item"
           :class="(selected === 'Tickets') || {{ request()->routeIs('tickets.*') ? 'true' : 'false' }} ? 'is-active' : ''"
           :title="sidebarToggle ? 'Tickets' : ''">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/>
          </svg>
          <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Tickets</span>
        </a>

        {{-- Orders --}}
        <a href="{{ route('orders.index') }}"
           x-show="hasPermission('orders')"
           @click="selected = 'Orders'"
           class="sb-item"
           :class="(selected === 'Orders') || {{ request()->routeIs('orders.*') ? 'true' : 'false' }} ? 'is-active' : ''"
           :title="sidebarToggle ? 'Orders' : ''">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/>
          </svg>
          <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Orders</span>
        </a>

        {{-- Attendees --}}
        <a href="{{ route('attendees.all') }}"
           x-show="hasPermission('attendees')"
           @click="selected = 'Attendees'"
           class="sb-item"
           :class="(selected === 'Attendees') || {{ request()->routeIs('attendees.*') ? 'true' : 'false' }} ? 'is-active' : ''"
           :title="sidebarToggle ? 'Attendees' : ''">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
          </svg>
          <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Attendees</span>
        </a>

        {{-- QR Codes --}}
        <a href="{{ route('qrcode.index') }}"
           x-show="hasPermission('qr-codes')"
           @click="selected = 'QR Codes'"
           class="sb-item"
           :class="(selected === 'QR Codes') || {{ request()->routeIs('qrcode.*') ? 'true' : 'false' }} ? 'is-active' : ''"
           :title="sidebarToggle ? 'QR Codes' : ''">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
            <path d="M6.75 7.5h.008v.008H6.75V7.5zM6.75 17.25h.008v.008H6.75v-.008zM16.5 7.5h.008v.008H16.5V7.5zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>
          </svg>
          <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">QR Codes</span>
        </a>
      </div>

      {{-- ========== FINANCE ========== --}}
      <div class="sb-group" x-show="hasPermission('finance') || hasPermission('budgets') || hasPermission('reports')">
        <p class="sb-group-label" :class="sidebarToggle ? 'lg:hidden' : ''">Finance</p>

        {{-- Finance --}}
        <a href="{{ route('finance.index') }}"
           x-show="hasPermission('finance')"
           @click="selected = 'Finance'"
           class="sb-item"
           :class="(selected === 'Finance') || {{ request()->routeIs('finance.*') ? 'true' : 'false' }} ? 'is-active' : ''"
           :title="sidebarToggle ? 'Finance' : ''">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Finance</span>
        </a>

        {{-- Budgets --}}
        <a href="{{ route('budgets.index') }}"
           x-show="hasPermission('budgets')"
           @click="selected = 'Budgets'"
           class="sb-item"
           :class="(selected === 'Budgets') || {{ request()->routeIs('budgets.*') ? 'true' : 'false' }} ? 'is-active' : ''"
           :title="sidebarToggle ? 'Budgets' : ''">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
          </svg>
          <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Budgets</span>
        </a>

        {{-- Reports --}}
        <a href="{{ route('reports') }}"
           x-show="hasPermission('reports')"
           @click="selected = 'Reports'"
           class="sb-item"
           :class="(selected === 'Reports') || {{ request()->routeIs('reports') ? 'true' : 'false' }} ? 'is-active' : ''"
           :title="sidebarToggle ? 'Reports' : ''">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
          </svg>
          <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Reports</span>
        </a>
      </div>

      {{-- ========== SYSTEM (admin) ========== --}}
      <div class="sb-group" x-show="hasPermission('system') || hasPermission('mpesa-callbacks') || hasPermission('users')">
        <p class="sb-group-label" :class="sidebarToggle ? 'lg:hidden' : ''">System</p>

        {{-- Users --}}
        <a href="{{ url('/users') }}"
           x-show="hasPermission('users')"
           @click="selected = 'Users'"
           class="sb-item"
           :class="(selected === 'Users') || {{ request()->is('users*') ? 'true' : 'false' }} ? 'is-active' : ''"
           :title="sidebarToggle ? 'Users' : ''">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
          </svg>
          <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Users</span>
        </a>

        {{-- M-Pesa Callbacks --}}
        <a href="{{ route('admin.mpesa-callbacks.index') }}"
           x-show="hasPermission('mpesa-callbacks')"
           @click="selected = 'M-Pesa Callbacks'"
           class="sb-item"
           :class="(selected === 'M-Pesa Callbacks') || {{ request()->routeIs('admin.mpesa-callbacks.*') ? 'true' : 'false' }} ? 'is-active' : ''"
           :title="sidebarToggle ? 'M-Pesa Callbacks' : ''">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
          </svg>
          <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">M-Pesa Callbacks</span>
        </a>

        {{-- System --}}
        <a href="{{ route('system.index') }}"
           x-show="hasPermission('system')"
           @click="selected = 'System'"
           class="sb-item"
           :class="(selected === 'System') || {{ request()->routeIs('system.*') ? 'true' : 'false' }} ? 'is-active' : ''"
           :title="sidebarToggle ? 'System' : ''">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">System</span>
        </a>
      </div>

    </nav>
  </div>

  {{-- ============ FOOTER: user card ============ --}}
  <div class="border-t border-gray-100 p-3 dark:border-gray-800">
    <div class="flex items-center gap-3 rounded-lg bg-gray-50 p-2.5 transition dark:bg-white/5"
         :class="sidebarToggle ? 'lg:justify-center' : ''">
      <img class="h-9 w-9 shrink-0 rounded-full object-cover ring-2 ring-white dark:ring-gray-800"
           src="{{ auth()->user()->avatar ? asset('storage/'.auth()->user()->avatar) : asset('images/user/user-01.jpg') }}"
           alt="{{ auth()->user()->name }}" />
      <div class="min-w-0 flex-1" :class="sidebarToggle ? 'lg:hidden' : ''">
        <p class="truncate text-sm font-semibold text-gray-800 dark:text-white/90">{{ auth()->user()->name }}</p>
        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
          {{ ucfirst(auth()->user()->getRoleNames()->first() ?? 'Member') }}
        </p>
      </div>
      <form method="POST" action="{{ route('logout') }}" :class="sidebarToggle ? 'lg:hidden' : ''">
        @csrf
        <button type="submit" title="Logout"
                class="rounded-md p-1.5 text-gray-400 transition hover:bg-gray-200 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
            <path d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
          </svg>
        </button>
      </form>
    </div>
  </div>
</aside>
@endauth