{{-- resources/views/experiences/create.blade.php --}}
@extends('layouts.app')

@section('content')
<main>
    <div x-data="{ pageName: 'New Experience' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-5xl md:p-6">

        <div class="mb-6 flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">New Experience</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Create a multi-day adventure with packages, itinerary and race categories.
                </p>
            </div>
            <a href="{{ route('experiences.index') }}"
               class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
        </div>

        <form id="experience-form" action="{{ route('experiences.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @if($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <strong>Please fix the following:</strong>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div id="form-errors" style="display:none;" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700"></div>

            {{-- SECTION 1: BASIC INFO --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Basic Information</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Name, description and when/where the experience happens.</p>
                </div>

                <div class="space-y-5 p-6">
                    <div>
                        <label for="name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Experience Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}"
                               placeholder="e.g. Serengeti Safari Marathon"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label for="short_description" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Short Description</label>
                        <input type="text" name="short_description" id="short_description" maxlength="500" value="{{ old('short_description') }}"
                               placeholder="A one-line tagline shown on cards"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label for="description" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Full Description</label>
                        <textarea name="description" id="description" rows="5" placeholder="Tell the full story of this adventure..."
                                  class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label for="location" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                        <input type="text" name="location" id="location" value="{{ old('location') }}" placeholder="e.g. Serengeti, Tanzania"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="start_date" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date &amp; Time</label>
                            <input type="datetime-local" name="start_date" id="start_date" value="{{ old('start_date') }}"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label for="end_date" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">End Date &amp; Time</label>
                            <input type="datetime-local" name="end_date" id="end_date" value="{{ old('end_date') }}"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="total_capacity" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Total Capacity</label>
                            <input type="number" name="total_capacity" id="total_capacity" min="1" value="{{ old('total_capacity') }}" placeholder="e.g. 300"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label for="status" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Status <span class="text-red-500">*</span></label>
                            <select name="status" id="status" required
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="draft"  {{ old('status') === 'draft'  ? 'selected' : '' }}>Draft</option>
                                <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>
                        <div class="flex items-end pb-2">
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="featured" value="1" {{ old('featured') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                Featured
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: PACKAGES --}}
            <div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900"
                 x-data="packagesSection()">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Packages</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Bronze / Silver / Gold / Platinum — the price tiers that change transport, accommodation and extras.</p>
                    </div>
                    <button type="button" @click="addPackage()"
                            class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3 py-2 text-sm font-medium text-white hover:bg-brand-600">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Add Package
                    </button>
                </div>

                <div class="space-y-4 p-6">
                    <template x-if="packages.length === 0">
                        <div class="rounded-lg border-2 border-dashed border-gray-200 p-8 text-center dark:border-gray-700">
                            <p class="text-sm text-gray-500 dark:text-gray-400">No packages yet. Click <strong>Add Package</strong> to define tiers.</p>
                        </div>
                    </template>

                    <template x-for="(pkg, index) in packages" :key="pkg.uid">
                        <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-800/30">
                            <div class="mb-4 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center rounded-md px-2.5 py-1 text-xs font-bold text-white"
                                          :style="`background-color: ${pkg.badge_color || '#9ca3af'};`"
                                          x-text="pkg.name || 'New'"></span>
                                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300" x-text="'Package #' + (index + 1)"></h3>
                                </div>
                                <button type="button" @click="removePackage(index)" class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Package <span class="text-red-500">*</span></label>
                                    <select :name="`packages[${index}][name]`" x-model="pkg.name" @change="onNameChange(index)"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        <option value="">— Select package —</option>
                                        <option value="Bronze">Bronze</option>
                                        <option value="Silver">Silver</option>
                                        <option value="Gold">Gold</option>
                                        <option value="Platinum">Platinum</option>
                                    </select>
                                    <input type="hidden" :name="`packages[${index}][tier]`" x-model="pkg.tier">
                                    <input type="hidden" :name="`packages[${index}][badge_color]`" x-model="pkg.badge_color">
                                </div>

                                <div class="grid grid-cols-3 gap-2">
                                    <div class="col-span-2">
                                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Price <span class="text-red-500">*</span></label>
                                        <input type="number" step="0.01" min="0" :name="`packages[${index}][price]`" x-model="pkg.price"
                                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Currency</label>
                                        <select :name="`packages[${index}][currency]`" x-model="pkg.currency"
                                                class="w-full rounded-lg border border-gray-300 px-2 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                            <option value="KES">KES</option>
                                            <option value="USD">USD</option>
                                            <option value="EUR">EUR</option>
                                            <option value="GBP">GBP</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Tagline</label>
                                    <input type="text" :name="`packages[${index}][tagline]`" x-model="pkg.tagline" placeholder="e.g. The essentials, done well"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Transport</label>
                                    <select :name="`packages[${index}][transport_type]`" x-model="pkg.transport_type"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        <option value="">— Select —</option>
                                        <option value="Shuttle bus">Shuttle bus</option>
                                        <option value="Private van">Private van</option>
                                        <option value="Private car">Private car</option>
                                        <option value="Flight">Flight</option>
                                        <option value="Train">Train</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Accommodation</label>
                                    <select :name="`packages[${index}][accommodation_type]`" x-model="pkg.accommodation_type"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        <option value="">— Select —</option>
                                        <option value="Hostel dorm">Hostel dorm</option>
                                        <option value="3★ lodge">3★ lodge</option>
                                        <option value="4★ lodge">4★ lodge</option>
                                        <option value="5★ suite">5★ luxury suite</option>
                                        <option value="Tented camp">Tented camp</option>
                                        <option value="Own arrangement">Own arrangement</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Room Sharing</label>
                                    <select :name="`packages[${index}][accommodation_sharing]`" x-model="pkg.accommodation_sharing"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        <option value="">— Select —</option>
                                        <option value="Shared">Shared</option>
                                        <option value="Private">Private</option>
                                        <option value="Suite">Suite</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Max Capacity</label>
                                    <input type="number" min="1" :name="`packages[${index}][max_capacity]`" x-model="pkg.max_capacity" placeholder="Leave empty for unlimited"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>

                                <div class="sm:col-span-2">
                                    <div class="mb-1 flex items-center justify-between">
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">What's Included</label>
                                        <button type="button" @click="addInclude(index)" class="text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">
                                            + Add item
                                        </button>
                                    </div>

                                    <template x-if="pkg.includes.length === 0">
                                        <p class="text-xs text-gray-400 italic py-2">No inclusions yet. Click <strong>+ Add item</strong> above.</p>
                                    </template>

                                    <div class="space-y-2">
                                        <template x-for="(item, ii) in pkg.includes" :key="ii">
                                            <div class="flex items-center gap-2">
                                                <span class="text-gray-400 select-none">•</span>
                                                <input type="text" :name="`packages[${index}][includes][]`" x-model="pkg.includes[ii]"
                                                       placeholder="e.g. Race entry, All meals"
                                                       class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                <button type="button" @click="removeInclude(index, ii)"
                                                        class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-3.5 w-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- SECTION 3: ITINERARY --}}
            <div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900"
                 x-data="itinerarySection()">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Itinerary</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Day-by-day schedule. Every registered member experiences all of this.</p>
                    </div>
                    <button type="button" @click="addDay()"
                            class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3 py-2 text-sm font-medium text-white hover:bg-brand-600">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Add Day
                    </button>
                </div>

                <div class="space-y-5 p-6">
                    <template x-if="days.length === 0">
                        <div class="rounded-lg border-2 border-dashed border-gray-200 p-8 text-center dark:border-gray-700">
                            <p class="text-sm text-gray-500 dark:text-gray-400">No itinerary yet. Click <strong>Add Day</strong> to build the schedule.</p>
                        </div>
                    </template>

                    <template x-for="(day, di) in days" :key="day.uid">
                        <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-800/30">
                            <div class="mb-4 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-brand-500 text-xs font-bold text-white" x-text="di + 1"></span>
                                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300" x-text="'Day ' + (di + 1) + (day.title ? ' — ' + day.title : '')"></h3>
                                </div>
                                <button type="button" @click="removeDay(di)" class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600" title="Remove day">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <input type="hidden" :name="`days[${di}][day_number]`" :value="di + 1">

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Day Title <span class="text-red-500">*</span></label>
                                    <input type="text" :name="`days[${di}][title]`" x-model="day.title" placeholder="e.g. Nairobi → Bunda"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Date</label>
                                    <input type="date" :name="`days[${di}][date]`" x-model="day.date"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Description</label>
                                    <textarea rows="2" :name="`days[${di}][description]`" x-model="day.description" placeholder="e.g. The road trip south begins before dawn..."
                                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                                </div>
                            </div>

                            <div class="mt-5 border-t border-gray-200 pt-4 dark:border-gray-700">
                                <div class="mb-3 flex items-center justify-between">
                                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Activities for Day <span x-text="di + 1"></span>
                                    </label>
                                    <button type="button" @click="addActivity(di)" class="text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">
                                        + Add Activity
                                    </button>
                                </div>

                                <template x-if="day.activities.length === 0">
                                    <p class="text-xs text-gray-400 italic py-2">No activities yet. Click <strong>+ Add Activity</strong> above.</p>
                                </template>

                                <div class="space-y-3">
                                    <template x-for="(act, ai) in day.activities" :key="act.uid">
                                        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                                            <div class="mb-3 flex items-center justify-between">
                                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400" x-text="'Activity ' + (ai + 1)"></span>
                                                <button type="button" @click="removeActivity(di, ai)" class="rounded-md p-1 text-gray-400 hover:bg-red-50 hover:text-red-600" title="Remove activity">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-3.5 w-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="mb-4">
                                                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Activity Image</label>
                                                <div class="flex items-center gap-3">
                                                    <div class="relative h-20 w-32 shrink-0 overflow-hidden rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800 flex items-center justify-center">
                                                        <template x-if="act.image_preview">
                                                            <img :src="act.image_preview" class="h-full w-full object-cover" alt="">
                                                        </template>
                                                        <template x-if="!act.image_preview">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                                 stroke="currentColor" stroke-width="1.5" class="h-6 w-6 text-gray-300 dark:text-gray-600">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/>
                                                            </svg>
                                                        </template>
                                                    </div>
                                                    <div class="flex-1">
                                                        <input type="file"
                                                               :name="`days[${di}][activities][${ai}][image]`"
                                                               accept="image/*"
                                                               @change="handleActivityImage($event, di, ai)"
                                                               class="w-full text-xs text-gray-600 dark:text-gray-300">
                                                        <p class="mt-1 text-xs text-gray-500">JPG / PNG, max 4MB</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Title <span class="text-red-500">*</span></label>
                                                    <input type="text" :name="`days[${di}][activities][${ai}][title]`" x-model="act.title" placeholder="e.g. Assemble & Depart Nairobi"
                                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Time Label</label>
                                                    <input type="text" :name="`days[${di}][activities][${ai}][time_label]`" x-model="act.time_label" placeholder="e.g. 05:30 AM, Daytime"
                                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                </div>
                                                <div class="sm:col-span-2">
                                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Description</label>
                                                    <textarea rows="2" :name="`days[${di}][activities][${ai}][description]`" x-model="act.description" placeholder="Describe what happens..."
                                                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                                                </div>
                                                <div class="sm:col-span-2">
                                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Tags <span class="text-gray-400">(comma separated)</span></label>
                                                    <input type="text" :name="`days[${di}][activities][${ai}][tags_text]`" x-model="act.tags_text" placeholder="e.g. Road Trip, Departure"
                                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                </div>
                                                <div class="sm:col-span-2">
                                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Pro Tip</label>
                                                    <input type="text" :name="`days[${di}][activities][${ai}][pro_tip]`" x-model="act.pro_tip" placeholder="e.g. Be at the assembly point by 5:00 AM"
                                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- SECTION 4: RACE CATEGORIES --}}
            <div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900"
                 x-data="categoriesSection()">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Race Categories</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Optional. 42km / 21km / 10km / 5km — leave empty if this isn't a race.</p>
                    </div>
                    <button type="button" @click="addCategory()"
                            class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3 py-2 text-sm font-medium text-white hover:bg-brand-600">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Add Category
                    </button>
                </div>

                <div class="space-y-3 p-6">
                    <template x-if="categories.length === 0">
                        <div class="rounded-lg border-2 border-dashed border-gray-200 p-8 text-center dark:border-gray-700">
                            <p class="text-sm text-gray-500 dark:text-gray-400">No race categories. That's fine if this experience isn't a race.</p>
                        </div>
                    </template>

                    <template x-for="(cat, ci) in categories" :key="cat.uid">
                        <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-700 dark:bg-gray-800/30">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-5">
                                <div class="sm:col-span-2">
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Name</label>
                                    <input type="text" :name="`categories[${ci}][name]`" x-model="cat.name" placeholder="42km Marathon"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Distance (km)</label>
                                    <input type="number" :name="`categories[${ci}][distance_km]`" x-model="cat.distance_km"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Surcharge</label>
                                    <input type="number" step="0.01" :name="`categories[${ci}][price_surcharge]`" x-model="cat.price_surcharge"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div class="flex items-end gap-2">
                                    <div class="flex-1">
                                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Cap</label>
                                        <input type="number" :name="`categories[${ci}][max_capacity]`" x-model="cat.max_capacity"
                                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    </div>
                                    <button type="button" @click="removeCategory(ci)"
                                            class="rounded-md p-2 text-gray-400 hover:bg-red-50 hover:text-red-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- SECTION 5: IMAGES --}}
            <div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Images</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Cover, poster and banner for the public landing page.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Cover Image</label>
                        <div class="aspect-video overflow-hidden rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800 flex items-center justify-center">
                            <img id="cover-preview" style="display:none;" class="w-full h-full object-cover" alt="">
                        </div>
                        <input type="file" name="image" id="image" accept="image/*" class="mt-2 w-full text-xs">
                        <p class="mt-1 text-xs text-gray-500">JPG / PNG, max 4MB</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Official Poster</label>
                        <div class="aspect-video overflow-hidden rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800 flex items-center justify-center">
                            <img id="poster-preview" style="display:none;" class="w-full h-full object-cover" alt="">
                        </div>
                        <input type="file" name="poster_image" id="poster_image" accept="image/*" class="mt-2 w-full text-xs">
                        <p class="mt-1 text-xs text-gray-500">JPG / PNG, max 4MB</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Wide Banner</label>
                        <div class="aspect-video overflow-hidden rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800 flex items-center justify-center">
                            <img id="banner-preview" style="display:none;" class="w-full h-full object-cover" alt="">
                        </div>
                        <input type="file" name="banner" id="banner" accept="image/*" class="mt-2 w-full text-xs">
                        <p class="mt-1 text-xs text-gray-500">JPG / PNG, max 4MB</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3 mb-10">
                <a href="{{ route('experiences.index') }}"
                   class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                    Cancel
                </a>
                <button type="submit" id="btn-submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50">
                    <span id="btn-submit-text">Create Experience</span>
                    <svg id="btn-spinner" style="display:none;" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity="0.25"/>
                        <path fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <div id="toast" style="display:none;" class="fixed bottom-6 right-6 z-[100000] rounded-lg px-4 py-3 text-sm font-medium text-white shadow-lg"></div>
</main>

<script>
// ============ Alpine: packages ============
function registerPackagesSection() {
    if (!window.Alpine || window.Alpine.data('packagesSection')) return;

    window.Alpine.data('packagesSection', () => ({
        packages: [],
        init() {
            const old = @json(old('packages') ?? []);
            if (Array.isArray(old) && old.length > 0) this.packages = old.map(p => this._normalize(p));
        },
        _normalize(p) {
            return {
                uid: 'pkg_' + Math.random().toString(36).slice(2),
                id: p.id || null,
                name: p.name || '',
                tier: p.tier || '',
                badge_color: p.badge_color || '',
                tagline: p.tagline || '',
                price: p.price || '',
                currency: p.currency || 'KES',
                transport_type: p.transport_type || '',
                accommodation_type: p.accommodation_type || '',
                accommodation_sharing: p.accommodation_sharing || '',
                max_capacity: p.max_capacity || '',
                includes: Array.isArray(p.includes) ? p.includes.slice() : [],
            };
        },
        addPackage() { this.packages.push(this._normalize({})); },
        removePackage(index) { if (confirm('Remove this package?')) this.packages.splice(index, 1); },
        addInclude(index) {
            const pkg = this.packages[index];
            this.packages[index] = { ...pkg, includes: [...pkg.includes, ''] };
        },
        removeInclude(index, ii) {
            const pkg = this.packages[index];
            this.packages[index] = { ...pkg, includes: pkg.includes.filter((_, i) => i !== ii) };
        },
        onNameChange(index) {
            const map = {
                'Bronze':   { tier: 1, color: '#CD7F32' },
                'Silver':   { tier: 2, color: '#A8A9AD' },
                'Gold':     { tier: 3, color: '#D4AF37' },
                'Platinum': { tier: 4, color: '#E5E4E2' },
            };
            const pkg = this.packages[index];
            const hit = map[pkg.name];
            if (hit) this.packages[index] = { ...pkg, tier: hit.tier, badge_color: hit.color };
        },
    }));
}

// ============ Alpine: itinerary ============
function registerItinerarySection() {
    if (!window.Alpine || window.Alpine.data('itinerarySection')) return;

    window.Alpine.data('itinerarySection', () => ({
        days: [],
        init() {
            const old = @json(old('days') ?? []);
            if (Array.isArray(old) && old.length > 0) this.days = old.map(d => this._normalizeDay(d));
        },
        _normalizeDay(d) {
            return {
                uid: 'day_' + Math.random().toString(36).slice(2),
                id: d.id || null,
                title: d.title || '',
                date: d.date || '',
                description: d.description || '',
                activities: Array.isArray(d.activities) ? d.activities.map(a => this._normalizeActivity(a)) : [],
            };
        },
        _normalizeActivity(a) {
            return {
                uid: 'act_' + Math.random().toString(36).slice(2),
                id: a.id || null,
                title: a.title || '',
                time_label: a.time_label || '',
                description: a.description || '',
                tags_text: Array.isArray(a.tags) ? a.tags.join(', ') : (a.tags_text || ''),
                pro_tip: a.pro_tip || '',
                image_preview: a.image_url || null,
            };
        },
        addDay() { this.days.push(this._normalizeDay({})); },
        removeDay(index) { if (confirm('Remove Day ' + (index + 1) + '?')) this.days.splice(index, 1); },
        addActivity(di) {
            const day = this.days[di];
            this.days[di] = { ...day, activities: [...day.activities, this._normalizeActivity({})] };
        },
        removeActivity(di, ai) {
            const day = this.days[di];
            this.days[di] = { ...day, activities: day.activities.filter((_, i) => i !== ai) };
        },
        handleActivityImage(event, di, ai) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => {
                const day = this.days[di];
                const activities = day.activities.map((a, i) =>
                    i === ai ? { ...a, image_preview: ev.target.result } : a
                );
                this.days[di] = { ...day, activities };
            };
            reader.readAsDataURL(file);
        },
    }));
}

// ============ Alpine: categories ============
function registerCategoriesSection() {
    if (!window.Alpine || window.Alpine.data('categoriesSection')) return;

    window.Alpine.data('categoriesSection', () => ({
        categories: [],
        init() {
            const old = @json(old('categories') ?? []);
            if (Array.isArray(old) && old.length > 0) this.categories = old.map(c => this._normalize(c));
        },
        _normalize(c) {
            return {
                uid: 'cat_' + Math.random().toString(36).slice(2),
                id: c.id || null,
                name: c.name || '',
                distance_km: c.distance_km || '',
                price_surcharge: c.price_surcharge || 0,
                max_capacity: c.max_capacity || '',
            };
        },
        addCategory() { this.categories.push(this._normalize({})); },
        removeCategory(i) { this.categories.splice(i, 1); },
    }));
}

function registerAllAlpine() {
    registerPackagesSection();
    registerItinerarySection();
    registerCategoriesSection();
}

if (window.Alpine) {
    registerAllAlpine();
} else {
    document.addEventListener('alpine:init', registerAllAlpine);
}

// ============ Plain JS: previews + submit ============
(function () {
    'use strict';
    const CSRF = '{{ csrf_token() }}';

    function bindPreview(inputId, previewId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => {
                const img = document.getElementById(previewId);
                img.src = ev.target.result;
                img.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    }
    bindPreview('image', 'cover-preview');
    bindPreview('poster_image', 'poster-preview');
    bindPreview('banner', 'banner-preview');

    document.getElementById('experience-form').addEventListener('submit', async function (e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);

        // Convert tags_text → tags[]
        const tagEntries = [];
        for (const [key, value] of formData.entries()) {
            if (key.endsWith('[tags_text]')) tagEntries.push([key, value]);
        }
        for (const [key, value] of tagEntries) {
            formData.delete(key);
            const base = key.replace('[tags_text]', '[tags]');
            const tags = String(value).split(',').map(t => t.trim()).filter(Boolean);
            tags.forEach(t => formData.append(base + '[]', t));
        }

        const btn  = document.getElementById('btn-submit');
        const txt  = document.getElementById('btn-submit-text');
        const spin = document.getElementById('btn-spinner');
        btn.disabled = true;
        txt.textContent = 'Saving...';
        spin.style.display = 'inline-block';

        const errBox = document.getElementById('form-errors');
        errBox.style.display = 'none';

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: formData,
            });

            const json = await res.json();

            if (json.success) {
                showToast('Experience created successfully');
                setTimeout(() => window.location.href = json.redirect || '/experiences', 800);
            } else {
                let html = `<strong>${json.message || 'Error'}</strong>`;
                if (json.errors) {
                    html += '<ul class="mt-2 list-disc pl-5">';
                    Object.values(json.errors).flat().forEach(m => { html += `<li>${m}</li>`; });
                    html += '</ul>';
                }
                errBox.innerHTML = html;
                errBox.style.display = 'block';
                errBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        } catch (err) {
            console.error(err);
            if (err instanceof SyntaxError) { form.submit(); return; }
            errBox.innerHTML = '<strong>Network error:</strong> ' + err.message;
            errBox.style.display = 'block';
        } finally {
            btn.disabled = false;
            txt.textContent = 'Create Experience';
            spin.style.display = 'none';
        }
    });

    function showToast(message, type = 'success') {
        const el = document.getElementById('toast');
        el.textContent = message;
        el.className = 'fixed bottom-6 right-6 z-[100000] rounded-lg px-4 py-3 text-sm font-medium text-white shadow-lg ' +
            (type === 'error' ? 'bg-red-600' : 'bg-green-600');
        el.style.display = 'block';
        clearTimeout(el._t);
        el._t = setTimeout(() => { el.style.display = 'none'; }, 3500);
    }
})();
</script>
@endsection