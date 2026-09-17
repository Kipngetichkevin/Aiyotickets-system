@extends('layouts.app')

@section('content')
<main>
    <div x-data="{ pageName: 'Create Event' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
        <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            @if(session('success'))
                <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                    <p class="font-medium mb-1">Please fix the following:</p>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Left Column - Event Details -->
                <div class="space-y-6">
                    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="px-5 py-4 sm:px-6 sm:py-5">
                            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Event Details</h3>
                        </div>
                        <div class="space-y-6 border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
                            <!-- Event Name -->
                            <div>
                                <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Event Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Enter event name"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Event Type -->
                            <div>
                                <label for="event_type_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Event Type <span class="text-red-500">*</span>
                                </label>
                                <div x-data="{ isOptionSelected: {{ old('event_type_id') ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                                    <select name="event_type_id" id="event_type_id" required
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                        :class="isOptionSelected && 'text-gray-800 dark:text-white/90'"
                                        @change="isOptionSelected = true">
                                        <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Select type</option>
                                        @foreach($eventTypes as $type)
                                            <option value="{{ $type->id }}" @selected(old('event_type_id') == $type->id) class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                                @error('event_type_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Currency -->
                            <div>
                                <label for="currency" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Currency <span class="text-red-500">*</span>
                                </label>
                                <div x-data="{ isOptionSelected: true }" class="relative z-20 bg-transparent">
                                    <select name="currency" id="currency" required
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                        <option value="KES" @selected(old('currency', 'KES') == 'KES') class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">KES</option>
                                        <option value="USD" @selected(old('currency') == 'USD') class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">USD</option>
                                        <option value="USDT" @selected(old('currency') == 'USDT') class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">USDT</option>
                                        <option value="EUROS" @selected(old('currency') == 'EUROS') class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">EUROS</option>
                                        <option value="POUND" @selected(old('currency') == 'POUND') class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">POUND</option>
                                    </select>
                                    <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                                @error('currency')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Event Dates - native date picker (matches tickets form) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Start Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="date" id="date"
                                        value="{{ old('date') }}" required
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    @error('date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="end_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        End Date
                                    </label>
                                    <input type="date" name="end_date" id="end_date"
                                        value="{{ old('end_date') }}"
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    @error('end_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Venue / Location -->
                            <div>
                                <label for="location" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Venue <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="location" id="location" value="{{ old('location') }}" required
                                        placeholder="Enter venue address or use Google Maps location"
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                    <button type="button" id="getLocationBtn"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1 text-xs text-white bg-blue-600 rounded hover:bg-blue-700 transition-colors">
                                        Get Location
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Click "Get Location" to use your current location
                                    </span>
                                </p>
                                @error('location')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <div x-data="{ isOptionSelected: true }" class="relative z-20 bg-transparent">
                                    <select name="status" id="status" required
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                        <option value="active" @selected(old('status', 'active') == 'active') class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Active</option>
                                        <option value="inactive" @selected(old('status') == 'inactive') class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Inactive</option>
                                        <option value="cancelled" @selected(old('status') == 'cancelled') class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Cancelled</option>
                                        <option value="completed" @selected(old('status') == 'completed') class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Completed</option>
                                    </select>
                                    <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Description
                                </label>
                                <textarea name="description" id="description" rows="6" placeholder="Enter event description"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Image Uploads -->
                <div class="space-y-6">
                    <!-- Image Upload -->
                    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="px-5 py-4 sm:px-6 sm:py-5">
                            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Event Image</h3>
                        </div>
                        <div class="space-y-6 border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
                            <div class="dropzone hover:border-brand-500! dark:hover:border-brand-500! rounded-xl border border-dashed! border-gray-300! bg-gray-50 p-7 lg:p-10 dark:border-gray-700! dark:bg-gray-900" id="event-image-upload">
                                <div class="dz-message m-0!">
                                    <div class="mb-[22px] flex justify-center">
                                        <div class="flex h-[68px] w-[68px] items-center justify-center rounded-full bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                            <svg class="fill-current" width="29" height="28" viewBox="0 0 29 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M14.5019 3.91699C14.2852 3.91699 14.0899 4.00891 13.953 4.15589L8.57363 9.53186C8.28065 9.82466 8.2805 10.2995 8.5733 10.5925C8.8661 10.8855 9.34097 10.8857 9.63396 10.5929L13.7519 6.47752V18.667C13.7519 19.0812 14.0877 19.417 14.5019 19.417C14.9161 19.417 15.2519 19.0812 15.2519 18.667V6.48234L19.3653 10.5929C19.6583 10.8857 20.1332 10.8855 20.426 10.5925C20.7188 10.2995 20.7186 9.82463 20.4256 9.53184L15.0838 4.19378C14.9463 4.02488 14.7367 3.91699 14.5019 3.91699ZM5.91626 18.667C5.91626 18.2528 5.58047 17.917 5.16626 17.917C4.75205 17.917 4.41626 18.2528 4.41626 18.667V21.8337C4.41626 23.0763 5.42362 24.0837 6.66626 24.0837H22.3339C23.5766 24.0837 24.5839 23.0763 24.5839 21.8337V18.667C24.5839 18.2528 24.2482 17.917 23.8339 17.917C23.4197 17.917 23.0839 18.2528 23.0839 18.667V21.8337C23.0839 22.2479 22.7482 22.5837 22.3339 22.5837H6.66626C6.25205 22.5837 5.91626 22.2479 5.91626 21.8337V18.667Z" fill=""/>
                                            </svg>
                                        </div>
                                    </div>

                                    <h4 class="text-theme-xl mb-3 font-semibold text-gray-800 dark:text-white/90">
                                        Drag & Drop File Here
                                    </h4>
                                    <span class="mx-auto mb-5 block w-full max-w-[290px] text-sm text-gray-700 dark:text-gray-400">
                                        Drag and drop your PNG, JPG, WebP, SVG images here or browse
                                    </span>

                                    <span class="text-theme-sm text-brand-500 font-medium underline">
                                        Browse File
                                    </span>
                                </div>
                            </div>
                            <input type="file" name="image" id="image" accept="image/*" class="hidden">
                            <div id="image-size-info" class="text-sm text-gray-500 dark:text-gray-400 hidden">
                                <span class="font-medium">File Size: </span>
                                <span id="image-file-size">0 KB</span>
                            </div>
                            @error('image')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Recommended size: 1200x630 pixels (max 2MB)
                            </p>
                        </div>
                    </div>

                    <!-- Banner Upload -->
                    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="px-5 py-4 sm:px-6 sm:py-5">
                            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Event Banner</h3>
                        </div>
                        <div class="space-y-6 border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
                            <div class="dropzone hover:border-brand-500! dark:hover:border-brand-500! rounded-xl border border-dashed! border-gray-300! bg-gray-50 p-7 lg:p-10 dark:border-gray-700! dark:bg-gray-900" id="event-banner-upload">
                                <div class="dz-message m-0!">
                                    <div class="mb-[22px] flex justify-center">
                                        <div class="flex h-[68px] w-[68px] items-center justify-center rounded-full bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                            <svg class="fill-current" width="29" height="28" viewBox="0 0 29 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M14.5019 3.91699C14.2852 3.91699 14.0899 4.00891 13.953 4.15589L8.57363 9.53186C8.28065 9.82466 8.2805 10.2995 8.5733 10.5925C8.8661 10.8855 9.34097 10.8857 9.63396 10.5929L13.7519 6.47752V18.667C13.7519 19.0812 14.0877 19.417 14.5019 19.417C14.9161 19.417 15.2519 19.0812 15.2519 18.667V6.48234L19.3653 10.5929C19.6583 10.8857 20.1332 10.8855 20.426 10.5925C20.7188 10.2995 20.7186 9.82463 20.4256 9.53184L15.0838 4.19378C14.9463 4.02488 14.7367 3.91699 14.5019 3.91699ZM5.91626 18.667C5.91626 18.2528 5.58047 17.917 5.16626 17.917C4.75205 17.917 4.41626 18.2528 4.41626 18.667V21.8337C4.41626 23.0763 5.42362 24.0837 6.66626 24.0837H22.3339C23.5766 24.0837 24.5839 23.0763 24.5839 21.8337V18.667C24.5839 18.2528 24.2482 17.917 23.8339 17.917C23.4197 17.917 23.0839 18.2528 23.0839 18.667V21.8337C23.0839 22.2479 22.7482 22.5837 22.3339 22.5837H6.66626C6.25205 22.5837 5.91626 22.2479 5.91626 21.8337V18.667Z" fill=""/>
                                            </svg>
                                        </div>
                                    </div>

                                    <h4 class="text-theme-xl mb-3 font-semibold text-gray-800 dark:text-white/90">
                                        Drag & Drop File Here
                                    </h4>
                                    <span class="mx-auto mb-5 block w-full max-w-[290px] text-sm text-gray-700 dark:text-gray-400">
                                        Drag and drop your PNG, JPG, WebP, SVG images here or browse
                                    </span>

                                    <span class="text-theme-sm text-brand-500 font-medium underline">
                                        Browse File
                                    </span>
                                </div>
                            </div>
                            <input type="file" name="banner" id="banner" accept="image/*" class="hidden">
                            <div id="banner-size-info" class="text-sm text-gray-500 dark:text-gray-400 hidden">
                                <span class="font-medium">File Size: </span>
                                <span id="banner-file-size">0 KB</span>
                            </div>
                            @error('banner')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Recommended size: 1920x1080 pixels (max 2MB)
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4 pt-6">
                <a href="{{ route('events.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-800">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Create Event
                </button>
            </div>
        </form>
    </div>
</main>

<!-- Dropzone CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>

<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/hdwhfaeo6baam0bcwi3t0g4if6viqjugkyvl1p5y583c5zkr/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

<script>
    // Initialize Dropzone
    Dropzone.autoDiscover = false;

    document.addEventListener('DOMContentLoaded', function () {

        // Get Location functionality
        const getLocationBtn = document.getElementById('getLocationBtn');
        const locationInput = document.getElementById('location');

        if (getLocationBtn && locationInput) {
            getLocationBtn.addEventListener('click', function () {
                if (navigator.geolocation) {
                    getLocationBtn.textContent = 'Getting...';
                    getLocationBtn.disabled = true;

                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;

                            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                                .then(r => r.json())
                                .then(data => {
                                    locationInput.value = (data && data.display_name) ? data.display_name : `${lat}, ${lng}`;
                                    getLocationBtn.textContent = 'Location Found ✓';
                                    getLocationBtn.disabled = false;
                                    setTimeout(() => getLocationBtn.textContent = 'Get Location', 3000);
                                })
                                .catch(() => {
                                    locationInput.value = `${lat}, ${lng}`;
                                    getLocationBtn.textContent = 'Location Found ✓';
                                    getLocationBtn.disabled = false;
                                    setTimeout(() => getLocationBtn.textContent = 'Get Location', 3000);
                                });
                        },
                        function (error) {
                            console.error('Geolocation error:', error);
                            getLocationBtn.textContent = 'Failed to get location';
                            getLocationBtn.disabled = false;
                            setTimeout(() => getLocationBtn.textContent = 'Get Location', 3000);
                        }
                    );
                } else {
                    alert('Geolocation is not supported by this browser.');
                }
            });
        }

        // Image Dropzone
        new Dropzone("#event-image-upload", {
            url: "{{ route('events.store') }}",
            autoProcessQueue: false,
            uploadMultiple: false,
            parallelUploads: 1,
            maxFiles: 1,
            maxFilesize: 2,
            acceptedFiles: 'image/*',
            addRemoveLinks: true,
            dictDefaultMessage: "Drop files here or click to upload",
            dictRemoveFile: "Remove",
            dictFileTooBig: "File is too big (2 MB max)",
            init: function () {
                this.on("addedfile", function (file) {
                    const fileInput = document.getElementById('image');
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                    file.previewElement.classList.add("dz-success");

                    const sizeInfo = document.getElementById('image-size-info');
                    const sizeDisplay = document.getElementById('image-file-size');
                    if (sizeInfo && sizeDisplay) {
                        sizeInfo.classList.remove('hidden');
                        const sizeInKB = (file.size / 1024).toFixed(2);
                        sizeDisplay.textContent = sizeInKB > 1024 ? (sizeInKB / 1024).toFixed(2) + ' MB' : sizeInKB + ' KB';
                    }
                });

                this.on("removedfile", function () {
                    document.getElementById('image').value = '';
                    const sizeInfo = document.getElementById('image-size-info');
                    if (sizeInfo) sizeInfo.classList.add('hidden');
                });
            }
        });

        // Banner Dropzone
        new Dropzone("#event-banner-upload", {
            url: "{{ route('events.store') }}",
            autoProcessQueue: false,
            uploadMultiple: false,
            parallelUploads: 1,
            maxFiles: 1,
            maxFilesize: 2,
            acceptedFiles: 'image/*',
            addRemoveLinks: true,
            dictDefaultMessage: "Drop files here or click to upload",
            dictRemoveFile: "Remove",
            dictFileTooBig: "File is too big (2 MB max)",
            init: function () {
                this.on("addedfile", function (file) {
                    const fileInput = document.getElementById('banner');
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                    file.previewElement.classList.add("dz-success");

                    const sizeInfo = document.getElementById('banner-size-info');
                    const sizeDisplay = document.getElementById('banner-file-size');
                    if (sizeInfo && sizeDisplay) {
                        sizeInfo.classList.remove('hidden');
                        const sizeInKB = (file.size / 1024).toFixed(2);
                        sizeDisplay.textContent = sizeInKB > 1024 ? (sizeInKB / 1024).toFixed(2) + ' MB' : sizeInKB + ' KB';
                    }
                });

                this.on("removedfile", function () {
                    document.getElementById('banner').value = '';
                    const sizeInfo = document.getElementById('banner-size-info');
                    if (sizeInfo) sizeInfo.classList.add('hidden');
                });
            }
        });

        // TinyMCE
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '#description',
                plugins: 'link image lists code table',
                toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
                menubar: false,
                height: 300,
                skin: 'oxide-dark',
                content_css: 'dark',
            });
        }
    });
</script>
@endsection