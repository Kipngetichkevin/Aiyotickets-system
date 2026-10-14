{{-- Public top bar (guests and public pages): no sidebar until you log in --}}
@php
    $cartCount = count((array) session('cart', [])) + count((array) session('experiences', []));
    $appName   = \App\Helpers\SystemHelper::appName();
    $navLink   = 'rounded-lg px-3 py-2 text-sm font-medium transition';
@endphp
{{-- No background at the top of the page; one fades in once the page scrolls under the bar --}}
<header x-data="{ open: false, scrolled: window.scrollY > 8 }" @scroll.window.passive="scrolled = window.scrollY > 8"
        class="sticky top-0 z-40 w-full transition-colors duration-200"
        :class="scrolled || open ? 'bg-white/90 shadow-theme-xs backdrop-blur dark:bg-gray-900/90' : 'bg-transparent'">
    <nav class="flex w-full items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center" aria-label="{{ $appName }}">
            <img src="{{ asset('images/logo/logo.svg') }}" alt="{{ $appName }}" class="h-9 w-auto dark:hidden">
            <img src="{{ asset('images/logo/logo-dark.svg') }}" alt="{{ $appName }}" class="hidden h-9 w-auto dark:block">
        </a>

        {{-- Everything else sits together on the right --}}
        <div class="flex items-center gap-1 sm:gap-2">
            {{-- On the home page these scroll to the section instead of loading a page --}}
            <a href="{{ route('home') }}#events"
               class="{{ $navLink }} hidden text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 md:inline-flex">Events</a>
            <a href="{{ route('home') }}#experiences"
               class="{{ $navLink }} hidden text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 md:inline-flex">Experiences</a>
            <a href="{{ route('create-event') }}"
               class="{{ $navLink }} hidden text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 md:inline-flex">Create event</a>
            <span class="mx-1 hidden h-6 w-px bg-gray-200 dark:bg-gray-700 md:block"></span>

            {{-- Cart --}}
            <a href="{{ route('cart.view') }}" class="relative rounded-lg p-2 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800" aria-label="Cart">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                </svg>
                @if ($cartCount > 0)
                    <span class="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-brand-500 px-1 text-[11px] font-bold text-white">{{ $cartCount }}</span>
                @endif
            </a>

            @auth
                @if (auth()->user()->isStaff())
                    <a href="{{ route('dashboard') }}" class="hidden rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 sm:inline-flex">Dashboard</a>
                @else
                    {{-- Client account menu --}}
                    <div x-data="{ menu: false }" @click.outside="menu = false" class="relative hidden sm:block">
                        <button type="button" @click="menu = !menu"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-500 text-xs font-bold text-white">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                            {{ \Illuminate\Support\Str::of(auth()->user()->name)->before(' ') }}
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="menu" x-cloak x-transition
                             class="absolute right-0 mt-2 w-52 overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-800 dark:bg-gray-900">
                            <a href="{{ route('my-qrs') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">My tickets</a>
                            <a href="{{ route('instalments.mine') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">My instalments</a>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">Profile</a>
                            <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100 dark:border-gray-800">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">Log out</button>
                            </form>
                        </div>
                    </div>
                @endif
            @else
                <a href="{{ route('login') }}" class="hidden rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800 sm:inline-flex">Log in</a>
                <a href="{{ route('register') }}" class="hidden rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 sm:inline-flex">Sign up</a>
            @endauth

            {{-- Mobile menu button --}}
            <button type="button" @click="open = !open" class="rounded-lg p-2 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 md:hidden" aria-label="Menu">
                <svg x-show="!open" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="open" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </nav>

    {{-- Mobile menu --}}
    <div x-show="open" x-cloak x-transition class="border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900 md:hidden">
        <div class="flex flex-col gap-1">
            <a href="{{ route('home') }}#events" @click="open = false" class="{{ $navLink }} text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">Events</a>
            <a href="{{ route('home') }}#experiences" @click="open = false" class="{{ $navLink }} text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">Experiences</a>
            <a href="{{ route('create-event') }}" class="{{ $navLink }} text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">Create event</a>
            @auth
                @if (auth()->user()->isStaff())
                    <a href="{{ route('dashboard') }}" class="mt-2 rounded-lg bg-brand-500 px-4 py-2.5 text-center text-sm font-semibold text-white">Dashboard</a>
                @else
                    <div class="mt-2 border-t border-gray-100 pt-2 dark:border-gray-800">
                        <a href="{{ route('my-qrs') }}" class="{{ $navLink }} block text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">My tickets</a>
                        <a href="{{ route('instalments.mine') }}" class="{{ $navLink }} block text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">My instalments</a>
                        <a href="{{ route('profile.edit') }}" class="{{ $navLink }} block text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button type="submit" class="{{ $navLink }} block w-full text-left text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">Log out</button>
                        </form>
                    </div>
                @endif
            @else
                <div class="mt-2 grid grid-cols-2 gap-2">
                    <a href="{{ route('login') }}" class="rounded-lg border border-gray-200 px-4 py-2.5 text-center text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-center text-sm font-semibold text-white">Sign up</a>
                </div>
            @endauth
        </div>
    </div>
</header>
