{{-- resources/views/qrcode/gate-pin.blade.php --}}
@extends('layouts.gate')

@section('title', 'Enter PIN')

@section('content')
<div class="mx-auto flex min-h-[70vh] max-w-sm flex-col justify-center px-4">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 text-center shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <p class="text-xs font-semibold uppercase tracking-wider text-brand-600">{{ $pass->label }}</p>
        <h1 class="mt-2 text-xl font-bold text-gray-900 dark:text-white">{{ $pass->targetName() }}</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Enter the gate PIN the organiser gave you.</p>
        <form method="POST" action="{{ route('gate.unlock', request()->route('token')) }}" class="mt-6 space-y-3">
            @csrf
            <input name="pin" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" autofocus required
                   class="h-14 w-full rounded-xl border border-gray-300 bg-transparent text-center text-2xl font-bold tracking-[0.5em] dark:border-gray-700 dark:text-white">
            @error('pin')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            <button class="w-full rounded-xl bg-brand-500 py-3 text-sm font-bold text-white hover:bg-brand-600">Open scanner</button>
        </form>
    </div>
</div>
@endsection
