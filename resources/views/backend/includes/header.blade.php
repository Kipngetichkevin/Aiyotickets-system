<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Aiyo Tickets Admin</title>

  <link rel="shortcut icon" href="{{ asset('storage/favicons/favicon.ico') }}">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <script>
    const html = document.querySelector('html');
    const isLightOrAuto = localStorage.getItem('hs_theme') === 'light' || (localStorage.getItem('hs_theme') === 'auto' && !window.matchMedia('(prefers-color-scheme: dark)').matches);
    const isDarkOrAuto = localStorage.getItem('hs_theme') === 'dark' || (localStorage.getItem('hs_theme') === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    if (isLightOrAuto && html.classList.contains('dark')) html.classList.remove('dark');
    else if (isDarkOrAuto && html.classList.contains('light')) html.classList.remove('light');
    else if (isDarkOrAuto && !html.classList.contains('dark')) html.classList.add('dark');
    else if (isLightOrAuto && !html.classList.contains('light')) html.classList.add('light');
  </script>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts/dist/apexcharts.min.css">
  <link rel="stylesheet" href="https://preline.co/assets/css/main.min.css">
  @vite(['resources/css/app.css'])

  <style>
    /* ============ GLOBAL RESET ============ */
    * { box-sizing: border-box; }
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-size: 14px;
        color: #1f2937;
        background: #f9fafb;
        overflow-x: hidden;
    }

    /* ============ HEADER ============ */
    header.app-header {
        position: fixed !important;
        top: 0 !important;
        left: 260px !important;
        right: 0 !important;
        height: 64px !important;
        width: auto !important;
        margin: 0 !important;
        padding: 0 24px !important;
        background: #ffffff !important;
        border-bottom: 1px solid #e5e7eb !important;
        z-index: 30 !important;
        display: flex !important;
        align-items: center;
    }

    header.app-header nav {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
    }

    /* ============ MAIN ============ */
    main.app-main {
        margin-left: 260px !important;
        margin-top: 0 !important;
        padding-top: 64px !important;
        width: auto !important;
        min-height: 100vh !important;
        background: #f9fafb;
    }

    @media (max-width: 1023px) {
        header.app-header {
            left: 0 !important;
            padding: 0 16px !important;
        }
        main.app-main {
            margin-left: 0 !important;
        }
    }

    /* ============ ICON BUTTONS ============ */
    .app-icon-btn {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        border: 1px solid transparent;
        background: transparent;
        color: #1f2937;
        cursor: pointer;
        padding: 0;
        text-decoration: none;
    }
    .app-icon-btn:hover { background-color: #f3f4f6; }
    .app-icon-btn svg { width: 18px; height: 18px; flex-shrink: 0; }

    /* ============ AVATAR ============ */
    .app-avatar-btn {
        width: 40px;
        height: 40px;
        padding: 0;
        border: 2px solid transparent;
        background: transparent;
        border-radius: 9999px;
        cursor: pointer;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: border-color 0.15s;
    }
    .app-avatar-btn:hover { border-color: #0ea5e9; }
    .app-avatar-img {
        width: 40px !important;
        height: 40px !important;
        border-radius: 9999px !important;
        object-fit: cover !important;
        display: block !important;
    }

    /* ============ DROPDOWN MENU ============ */
    .app-menu-wrapper {
        position: relative;
        display: inline-flex;
    }

    .app-menu-panel {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        min-width: 240px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 10px 24px rgba(0,0,0,0.10);
        padding: 8px;
        display: none;
        z-index: 100;
    }

    .app-menu-panel.is-open {
        display: block;
        animation: appMenuFade 0.12s ease-out;
    }

    @keyframes appMenuFade {
        from { opacity: 0; transform: translateY(-4px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .app-menu-header {
        padding: 10px 12px;
        border-bottom: 1px solid #f3f4f6;
        margin-bottom: 6px;
    }
    .app-menu-header .name {
        font-size: 13px;
        font-weight: 700;
        color: #111827;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .app-menu-header .email {
        font-size: 11px;
        color: #6b7280;
        margin: 2px 0 0 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .app-menu-item {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 9px 12px;
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        background: none;
        border: none;
        border-radius: 8px;
        text-decoration: none;
        text-align: left;
        cursor: pointer;
        font-family: inherit;
    }
    .app-menu-item:hover {
        background: #f3f4f6;
    }
    .app-menu-item.danger {
        color: #dc2626;
    }
    .app-menu-item.danger:hover {
        background: #fee2e2;
        color: #b91c1c;
    }
    .app-menu-item svg {
        width: 15px;
        height: 15px;
        flex-shrink: 0;
    }

    /* ============ MOBILE LOGO ============ */
    .app-mobile-logo { display: none; margin-right: auto; }
    .app-mobile-logo img { height: 32px !important; width: auto !important; display: block; }
    @media (max-width: 1023px) { .app-mobile-logo { display: block; } }
  </style>
</head>

<body>

  <header class="app-header">
    <nav>

      {{-- Mobile logo --}}
      <div class="app-mobile-logo">
        <a href="/" style="display: inline-block; text-decoration: none;">
          <img src="{{ asset('images/logo.svg') }}" alt="Aiyo Tickets Admin">
        </a>
      </div>

      {{-- Notifications --}}
      <button type="button" class="app-icon-btn" title="Notifications">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
          <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
        </svg>
      </button>

      {{-- Activity --}}
      <button type="button" class="app-icon-btn" title="Activity">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
        </svg>
      </button>

      {{-- Avatar + dropdown --}}
      <div class="app-menu-wrapper">
        <button type="button"
                id="appAvatarBtn"
                class="app-avatar-btn"
                title="Your account"
                aria-haspopup="menu"
                aria-expanded="false"
                onclick="toggleAppMenu(event)">
          <img
            src="{{ auth()->user()->avatar ?? 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?ixlib=rb-4.0.3&auto=format&fit=facearea&facepad=2&w=80&h=80&q=80' }}"
            alt="Avatar"
            class="app-avatar-img">
        </button>

        <div id="appMenuPanel" class="app-menu-panel" role="menu">

          {{-- Current user --}}
          <div class="app-menu-header">
            <p class="name">{{ auth()->user()->name ?? 'User' }}</p>
            <p class="email">{{ auth()->user()->email ?? '' }}</p>
          </div>

          {{-- Profile --}}
          <a href="{{ route('profile.edit') }}" class="app-menu-item" role="menuitem">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="12" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Profile
          </a>

          {{-- Dashboard --}}
          <a href="{{ route('dashboard') }}" class="app-menu-item" role="menuitem">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke-linecap="round" stroke-linejoin="round"/>
              <polyline points="9 22 9 12 15 12 15 22" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Dashboard
          </a>

          <div style="height: 1px; background: #f3f4f6; margin: 6px 0;"></div>

          {{-- Sign out --}}
          <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
            @csrf
            <button type="submit" class="app-menu-item danger" role="menuitem">
              <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke-linecap="round" stroke-linejoin="round"/>
                <polyline points="16 17 21 12 16 7" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="21" y1="12" x2="9" y2="12" stroke-linecap="round"/>
              </svg>
              Sign out
            </button>
          </form>

        </div>
      </div>

    </nav>
  </header>

  <script>
    function toggleAppMenu(event) {
      event.stopPropagation();
      const panel = document.getElementById('appMenuPanel');
      const btn   = document.getElementById('appAvatarBtn');
      const open  = panel.classList.toggle('is-open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    // Close when clicking outside
    document.addEventListener('click', function (e) {
      const panel = document.getElementById('appMenuPanel');
      const btn   = document.getElementById('appAvatarBtn');
      if (!panel || !btn) return;
      if (!panel.contains(e.target) && !btn.contains(e.target)) {
        panel.classList.remove('is-open');
        btn.setAttribute('aria-expanded', 'false');
      }
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        const panel = document.getElementById('appMenuPanel');
        const btn   = document.getElementById('appAvatarBtn');
        if (panel) panel.classList.remove('is-open');
        if (btn)   btn.setAttribute('aria-expanded', 'false');
      }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/preline@1.8.2/dist/preline.js"></script>