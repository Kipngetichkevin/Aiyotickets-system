<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verify Email | {{ \App\Helpers\SystemHelper::appName() }}</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>
<body
    x-data="{ darkMode: false }"
    x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode'));
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark bg-gray-900': darkMode === true}"
>

<div class="relative z-1 flex h-screen w-full overflow-hidden bg-white dark:bg-gray-900">

    <div class="flex flex-1 flex-col rounded-2xl p-6 sm:rounded-none sm:border-0 sm:p-8">
        <div class="mx-auto w-full max-w-md pt-5 sm:py-10">
            <a href="{{ route('home') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                ← Back to home
            </a>
        </div>

        <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center">
            <div class="mb-5 sm:mb-8">
                <h1 class="mb-2 text-title-sm font-semibold text-gray-800 dark:text-white/90 sm:text-title-md">
                    Verify Your Email
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Thanks for signing up! Before getting started, please verify your email address by clicking on the link we just emailed to you. If you didn't receive the email, we will gladly send you another.
                </p>
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-5 rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm font-medium text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">
                    A new verification link has been sent to the email address you provided during registration.
                </div>
            @endif

            <div class="space-y-5">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button
                        type="submit"
                        class="flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                    >
                        Resend Verification Email
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="text-center">
                    @csrf
                    <button type="submit" class="text-sm text-brand-500 hover:text-brand-600 dark:text-brand-400">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="relative z-1 hidden flex-1 items-center justify-center bg-brand-950 p-8 dark:bg-white/5 lg:flex">
        <div class="flex max-w-xs flex-col items-center">
            <a href="{{ route('home') }}" class="mb-4 block">
                <img
                    class="h-40 w-auto"
                    src="{{ \App\Helpers\SystemHelper::logoUrl(true) ?? asset('images/logo/auth-logo-dark.svg') }}"
                    alt="{{ \App\Helpers\SystemHelper::appName() }} Logo"
                />
            </a>
            <p class="text-center text-gray-400 dark:text-white/60">{{ \App\Helpers\SystemHelper::slogan() }}</p>
        </div>
    </div>

</div>

</body>
</html>
