@extends('layouts.app')

@section('content')
<main>
    <div x-data="{ pageName: 'Create Event' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
        <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Hidden Fields -->
            <input type="hidden" name="merchant_id" value="{{ auth()->id() }}">
            <input type="hidden" name="user_id" value="{{ auth()->id() }}">

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
                                <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
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
                                <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                                    <select name="currency" id="currency" required
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                        :class="isOptionSelected && 'text-gray-800 dark:text-white/90'"
                                        @change="isOptionSelected = true">
                                        <option value="KES" @selected(old('currency') == 'KES') class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">KES</option>
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

                            <!-- Event Dates with Flatpickr -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Start Date <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="text" name="date" id="date" value="{{ old('date') }}" required
                                            placeholder="Select start date"
                                            class="flatpickr-input dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                        <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                            <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.66659 1.5415C7.0808 1.5415 7.41658 1.87729 7.41658 2.2915V2.99984H12.5833V2.2915C12.5833 1.87729 12.919 1.5415 13.3333 1.5415C13.7475 1.5415 14.0833 1.87729 14.0833 2.2915V2.99984L15.4166 2.99984C16.5212 2.99984 17.4166 3.89527 17.4166 4.99984V7.49984V15.8332C17.4166 16.9377 16.5212 17.8332 15.4166 17.8332H4.58325C3.47868 17.8332 2.58325 16.9377 2.58325 15.8332V7.49984V4.99984C2.58325 3.89527 3.47868 2.99984 4.58325 2.99984L5.91659 2.99984V2.2915C5.91659 1.87729 6.25237 1.5415 6.66659 1.5415ZM6.66659 4.49984H4.58325C4.30711 4.49984 4.08325 4.7237 4.08325 4.99984V6.74984H15.9166V4.99984C15.9166 4.7237 15.6927 4.49984 15.4166 4.49984H13.3333H6.66659ZM15.9166 8.24984H4.08325V15.8332C4.08325 16.1093 4.30711 16.3332 4.58325 16.3332H15.4166C15.6927 16.3332 15.9166 16.1093 15.9166 15.8332V8.24984Z" fill=""/>
                                            </svg>
                                        </span>
                                    </div>
                                    @error('date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="end_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        End Date
                                    </label>
                                    <div class="relative">
                                        <input type="text" name="end_date" id="end_date" value="{{ old('end_date') }}"
                                            placeholder="Select end date"
                                            class="flatpickr-input dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                        <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                            <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.66659 1.5415C7.0808 1.5415 7.41658 1.87729 7.41658 2.2915V2.99984H12.5833V2.2915C12.5833 1.87729 12.919 1.5415 13.3333 1.5415C13.7475 1.5415 14.0833 1.87729 14.0833 2.2915V2.99984L15.4166 2.99984C16.5212 2.99984 17.4166 3.89527 17.4166 4.99984V7.49984V15.8332C17.4166 16.9377 16.5212 17.8332 15.4166 17.8332H4.58325C3.47868 17.8332 2.58325 16.9377 2.58325 15.8332V7.49984V4.99984C2.58325 3.89527 3.47868 2.99984 4.58325 2.99984L5.91659 2.99984V2.2915C5.91659 1.87729 6.25237 1.5415 6.66659 1.5415ZM6.66659 4.49984H4.58325C4.30711 4.49984 4.08325 4.7237 4.08325 4.99984V6.74984H15.9166V4.99984C15.9166 4.7237 15.6927 4.49984 15.4166 4.49984H13.3333H6.66659ZM15.9166 8.24984H4.08325V15.8332C4.08325 16.1093 4.30711 16.3332 4.58325 16.3332H15.4166C15.6927 16.3332 15.9166 16.1093 15.9166 15.8332V8.24984Z" fill=""/>
                                            </svg>
                                        </span>
                                    </div>
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
                                <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                                    <select name="status" id="status" required
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                        :class="isOptionSelected && 'text-gray-800 dark:text-white/90'"
                                        @change="isOptionSelected = true">
                                        <option value="active" @selected(old('status') == 'active') class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Active</option>
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

<!-- Include Flatpickr CSS & JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">

<!-- Include Dropzone JS -->
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />

<!-- Include TinyMCE -->
<script src="https://cdn.tiny.cloud/1/hdwhfaeo6baam0bcwi3t0g4if6viqjugkyvl1p5y583c5zkr/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

<!-- Include Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    // Initialize Dropzone
    Dropzone.autoDiscover = false;
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Flatpickr for dates
        flatpickr("#date", {
            enableTime: true,
            dateFormat: "Y-m-d H:i:S",
            time_24hr: true,
            minDate: "today",
            placeholder: "Select start date",
            onChange: function(selectedDates, dateStr, instance) {
                // Update end date min date
                if (selectedDates.length > 0) {
                    const endPicker = document.querySelector("#end_date")._flatpickr;
                    if (endPicker) {
                        endPicker.set("minDate", selectedDates[0]);
                    }
                }
            }
        });

        flatpickr("#end_date", {
            enableTime: true,
            dateFormat: "Y-m-d H:i:S",
            time_24hr: true,
            minDate: "today",
            placeholder: "Select end date"
        });

        // Get Location functionality
        const getLocationBtn = document.getElementById('getLocationBtn');
        const locationInput = document.getElementById('location');
        
        if (getLocationBtn && locationInput) {
            getLocationBtn.addEventListener('click', function() {
                if (navigator.geolocation) {
                    getLocationBtn.textContent = 'Getting...';
                    getLocationBtn.disabled = true;
                    
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            
                            // Reverse geocode to get address
                            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                                .then(response => response.json())
                                .then(data => {
                                    if (data && data.display_name) {
                                        locationInput.value = data.display_name;
                                    } else {
                                        locationInput.value = `${lat}, ${lng}`;
                                    }
                                    getLocationBtn.textContent = 'Location Found ✓';
                                    getLocationBtn.disabled = false;
                                    setTimeout(() => {
                                        getLocationBtn.textContent = 'Get Location';
                                    }, 3000);
                                })
                                .catch(() => {
                                    locationInput.value = `${lat}, ${lng}`;
                                    getLocationBtn.textContent = 'Location Found ✓';
                                    getLocationBtn.disabled = false;
                                    setTimeout(() => {
                                        getLocationBtn.textContent = 'Get Location';
                                    }, 3000);
                                });
                        },
                        function(error) {
                            console.error('Geolocation error:', error);
                            getLocationBtn.textContent = 'Failed to get location';
                            getLocationBtn.disabled = false;
                            setTimeout(() => {
                                getLocationBtn.textContent = 'Get Location';
                            }, 3000);
                        }
                    );
                } else {
                    alert('Geolocation is not supported by this browser.');
                }
            });
        }

  // Image Dropzone
  const imageDropzone = new Dropzone("#event-image-upload", {
      url: "{{ route('events.store') }}",
      autoProcessQueue: false,
      uploadMultiple: false,
      parallelUploads: 1,
      maxFiles: 1,
      maxFilesize: 2, // Size in MB
      acceptedFiles: 'image/*',
      addRemoveLinks: true,
      dictDefaultMessage: "Drop files here or click to upload",
      dictRemoveFile: "Remove",
      dictFileTooBig: "File is too big (2 MB max)",
      init: function() {
          this.on("addedfile", function(file) {
              const fileInput = document.getElementById('image');
              const dataTransfer = new DataTransfer();
              dataTransfer.items.add(file);
              fileInput.files = dataTransfer.files;
              file.previewElement.classList.add("dz-success");
              
              // Show file size
              const sizeInfo = document.getElementById('image-size-info');
              const sizeDisplay = document.getElementById('image-file-size');
              if (sizeInfo && sizeDisplay) {
                  sizeInfo.classList.remove('hidden');
                  const sizeInKB = (file.size / 1024).toFixed(2);
                  if (sizeInKB > 1024) {
                      sizeDisplay.textContent = (sizeInKB / 1024).toFixed(2) + ' MB';
                  } else {
                      sizeDisplay.textContent = sizeInKB + ' KB';
                  }
              }
          });
          
          this.on("removedfile", function(file) {
              document.getElementById('image').value = '';
              const sizeInfo = document.getElementById('image-size-info');
              if (sizeInfo) {
                  sizeInfo.classList.add('hidden');
              }
          });
          
          this.on("error", function(file, errorMessage) {
              console.error('Dropzone error:', errorMessage);
          });
      }
  });

  // Banner Dropzone
  const bannerDropzone = new Dropzone("#event-banner-upload", {
      url: "{{ route('events.store') }}",
      autoProcessQueue: false,
      uploadMultiple: false,
      parallelUploads: 1,
      maxFiles: 1,
      maxFilesize: 2, // Size in MB
      acceptedFiles: 'image/*',
      addRemoveLinks: true,
      dictDefaultMessage: "Drop files here or click to upload",
      dictRemoveFile: "Remove",
      dictFileTooBig: "File is too big (2 MB max)",
      init: function() {
          this.on("addedfile", function(file) {
              const fileInput = document.getElementById('banner');
              const dataTransfer = new DataTransfer();
              dataTransfer.items.add(file);
              fileInput.files = dataTransfer.files;
              file.previewElement.classList.add("dz-success");
              
              // Show file size
              const sizeInfo = document.getElementById('banner-size-info');
              const sizeDisplay = document.getElementById('banner-file-size');
              if (sizeInfo && sizeDisplay) {
                  sizeInfo.classList.remove('hidden');
                  const sizeInKB = (file.size / 1024).toFixed(2);
                  if (sizeInKB > 1024) {
                      sizeDisplay.textContent = (sizeInKB / 1024).toFixed(2) + ' MB';
                  } else {
                      sizeDisplay.textContent = sizeInKB + ' KB';
                  }
              }
          });
          
          this.on("removedfile", function(file) {
              document.getElementById('banner').value = '';
              const sizeInfo = document.getElementById('banner-size-info');
              if (sizeInfo) {
                  sizeInfo.classList.add('hidden');
              }
          });
          
          this.on("error", function(file, errorMessage) {
              console.error('Dropzone error:', errorMessage);
          });
      }
  });

        // Initialize TinyMCE
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '#description',
                plugins: 'link image lists code table',
                toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
                menubar: false,
                height: 300,
                skin: 'oxide-dark',
                content_css: 'dark',
                setup: function(editor) {
                    editor.on('init', function() {
                        editor.getContainer().style.backgroundColor = '#1f2937';
                        editor.getContainer().style.color = '#e5e7eb';
                    });
                }
            });
        }
    });
</script>
@endsection