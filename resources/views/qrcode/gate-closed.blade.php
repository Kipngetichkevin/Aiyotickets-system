{{-- resources/views/qrcode/gate-closed.blade.php --}}
@extends('layouts.gate')

@section('title', 'Gate link closed')

@section('content')
<div class="mx-auto flex min-h-[70vh] max-w-sm flex-col justify-center px-4 text-center">
    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-gray-500 dark:bg-gray-800">
        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
    </div>
    <h1 class="mt-5 text-xl font-bold text-gray-900 dark:text-white">This gate link is closed</h1>
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">It has expired or the organiser switched it off. Ask them for a new link.</p>
</div>
@endsection
