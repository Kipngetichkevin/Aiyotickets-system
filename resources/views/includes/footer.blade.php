{{-- Public site footer --}}
@php
    $appName = \App\Helpers\SystemHelper::appName();
    $email   = \App\Helpers\SystemHelper::contactEmail();
    $phone   = \App\Helpers\SystemHelper::contactPhone();
    $socials = \App\Helpers\SystemHelper::socialMedia();
    $link    = 'text-sm text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white';
@endphp
<footer class="border-t border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 gap-10 md:grid-cols-4 lg:grid-cols-5">
            <div class="col-span-2">
                <a href="{{ route('home') }}" aria-label="{{ $appName }}">
                    <img src="{{ asset('images/logo/logo.svg') }}" alt="{{ $appName }}" class="h-9 w-auto dark:hidden">
                    <img src="{{ asset('images/logo/logo-dark.svg') }}" alt="{{ $appName }}" class="hidden h-9 w-auto dark:block">
                </a>
                <p class="mt-4 max-w-xs text-sm text-gray-500 dark:text-gray-400">Tickets for events and experiences across Kenya. Pay with M-Pesa, get your QR ticket instantly.</p>
                @if ($email || $phone)
                    <p class="mt-4 space-y-1 text-sm text-gray-500 dark:text-gray-400">
                        @if ($email)<a href="mailto:{{ $email }}" class="block hover:text-gray-900 dark:hover:text-white">{{ $email }}</a>@endif
                        @if ($phone)<a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="block hover:text-gray-900 dark:hover:text-white">{{ $phone }}</a>@endif
                    </p>
                @endif
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Discover</h3>
                <ul class="mt-4 space-y-3">
                    <li><a href="{{ route('home') }}#events" class="{{ $link }}">Events</a></li>
                    <li><a href="{{ route('experiences.browse') }}" class="{{ $link }}">Experiences</a></li>
                    <li><a href="{{ route('my-qrs') }}" class="{{ $link }}">Find my tickets</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">For organisers</h3>
                <ul class="mt-4 space-y-3">
                    <li><a href="{{ route('create-event') }}" class="{{ $link }}">Create event</a></li>
                    <li><a href="{{ route('organisers.landing') }}#how-it-works" class="{{ $link }}">How it works</a></li>
                    <li><a href="{{ route('organisers.landing') }}#pricing" class="{{ $link }}">Pricing</a></li>
                    <li><a href="{{ route('login', ['redirect' => '/create-event']) }}" class="{{ $link }}">Organiser log in</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $appName }}</h3>
                <ul class="mt-4 space-y-3">
                    <li><a href="{{ route('terms') }}" class="{{ $link }}">Terms &amp; conditions</a></li>
                    @if ($email)
                        <li><a href="mailto:{{ $email }}" class="{{ $link }}">Contact us</a></li>
                    @endif
                    @foreach ($socials as $social)
                        <li><a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" class="{{ $link }}">{{ $social['name'] ?? ucfirst($social['platform'] ?? 'Social') }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="mt-12 flex flex-wrap items-center justify-between gap-4 border-t border-gray-100 pt-8 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
            <p>© {{ now()->year }} {{ $appName }}. All rights reserved.</p>
            <p>Payments by M-Pesa</p>
        </div>
    </div>
</footer>
