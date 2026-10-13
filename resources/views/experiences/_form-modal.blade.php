{{-- resources/views/experiences/_form-modal.blade.php --}}
<div x-data="experienceFormModal()"
     x-init="init()"
     x-show="isOpen"
     x-cloak
     @open-experience-modal.window="open($event.detail)"
     @keydown.escape.window="close()"
     class="fixed inset-0 z-[999999]"
     style="display:none;">

    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="close()"></div>

    <div class="fixed inset-y-0 right-0 flex max-w-full">
        <div class="relative w-screen max-w-3xl transform transition-transform duration-300 ease-in-out"
             :class="isOpen ? 'translate-x-0' : 'translate-x-full'">

            <form @submit.prevent="submit()"
                  class="flex h-full flex-col overflow-hidden bg-white shadow-xl dark:bg-gray-900">

                {{-- HEADER --}}
                <div class="flex shrink-0 items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white"
                            x-text="mode === 'edit' ? 'Edit Experience' : 'New Experience'"></h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"
                           x-text="mode === 'edit' ? 'Update packages, itinerary and race categories.' : 'Create a new adventure with packages and itinerary.'"></p>
                    </div>
                    <button type="button" @click="close()"
                            class="rounded-full bg-gray-100 p-2 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- BODY --}}
                <div class="flex-1 overflow-y-auto px-6 py-6">

                    <div x-show="loading" class="py-16 text-center">
                        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-brand-500 border-t-transparent"></div>
                        <p class="mt-3 text-sm text-gray-500">Loading experience…</p>
                    </div>

                    <div x-show="!loading" class="space-y-6">

                        {{-- Errors --}}
                        <div x-show="errors.length" x-cloak
                             class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
                            <p class="font-semibold mb-1">Please fix the following:</p>
                            <ul class="list-disc pl-5 space-y-0.5">
                                <template x-for="(err, i) in errors" :key="i">
                                    <li x-text="err"></li>
                                </template>
                            </ul>
                        </div>

                        {{-- 1. BASIC INFO --}}
                        <section class="rounded-xl border border-gray-200 dark:border-gray-800">
                            <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-800">
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Basic Information</h3>
                            </div>
                            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                        Experience Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" x-model="form.name" required
                                           placeholder="Serengeti Safari Marathon"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Short Description</label>
                                    <input type="text" x-model="form.short_description" maxlength="500"
                                           placeholder="Run wild"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Full Description</label>
                                    <textarea x-model="form.description" rows="3" placeholder="Tell the full story…"
                                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Location</label>
                                    <input type="text" x-model="form.location" placeholder="Serengeti, Tanzania"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Total Capacity</label>
                                    <input type="number" x-model.number="form.total_capacity" min="1" placeholder="300"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Start Date &amp; Time</label>
                                    <input type="datetime-local" x-model="form.start_date"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">End Date &amp; Time</label>
                                    <input type="datetime-local" x-model="form.end_date"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Status <span class="text-red-500">*</span></label>
                                    <select x-model="form.status" required
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        <option value="active">Active</option>
                                        <option value="draft">Draft</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                </div>
                                <div class="flex items-end pb-2">
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" x-model="form.featured"
                                               class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                        Featured
                                    </label>
                                </div>
                            </div>
                        </section>

                        {{-- 2. PACKAGES --}}
                        <section class="rounded-xl border border-gray-200 dark:border-gray-800">
                            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-800">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Packages</h3>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Bronze · Silver · Gold · Platinum</p>
                                </div>
                                <button type="button" @click="addPackage()"
                                        class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">
                                    + Add Package
                                </button>
                            </div>
                            <div class="p-5 space-y-4">
                                <template x-if="form.packages.length === 0">
                                    <p class="text-sm text-gray-400 text-center py-4">No packages yet. Click <strong>+ Add Package</strong>.</p>
                                </template>

                                <template x-for="(pkg, pi) in form.packages" :key="pkg.uid">
                                    <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-700 dark:bg-gray-800/30">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold text-white"
                                                      :style="`background-color: ${pkg.badge_color || '#9ca3af'};`"
                                                      x-text="pkg.name || 'New'"></span>
                                                <span class="text-xs text-gray-500" x-text="'Package #' + (pi + 1)"></span>
                                            </div>
                                            <button type="button" @click="removePackage(pi)"
                                                    class="text-xs text-red-600 hover:underline">Remove</button>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                                            {{-- Package Name --}}
                                            <div>
                                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Package</label>
                                                <select x-model="pkg.name" @change="onPackageNameChange(pi)"
                                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                    <option value="">— Select package —</option>
                                                    <option value="Bronze">Bronze</option>
                                                    <option value="Silver">Silver</option>
                                                    <option value="Gold">Gold</option>
                                                    <option value="Platinum">Platinum</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Tier</label>
                                                <input type="number" x-model.number="pkg.tier" min="1" max="10"
                                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                            </div>

                                            <div>
                                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Price (KES)</label>
                                                <input type="number" step="0.01" min="0" x-model.number="pkg.price"
                                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                            </div>

                                            <div class="sm:col-span-3">
                                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Tagline</label>
                                                <input type="text" x-model="pkg.tagline" placeholder="The essentials, done well"
                                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                            </div>

                                            {{-- TRANSPORT DROPDOWN --}}
                                            <div>
                                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Transport</label>
                                                <select x-model="pkg.transport_type" @change="onTransportChange(pi)"
                                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                    <option value="">— Select transport —</option>

                                                    <optgroup label="Budget">
                                                        <option value="Shared Matatu / Bus (Nairobi → Nanyuki)">Shared Matatu / Bus (Nairobi → Nanyuki) ~KES 700–1,000</option>
                                                        <option value="Shared Shuttle Van">Shared Shuttle Van (Impala Shuttle, etc.)</option>
                                                        <option value="Public Bus (Nyamakima)">Public Bus (Nyamakima terminal)</option>
                                                    </optgroup>

                                                    <optgroup label="Standard">
                                                        <option value="Private Car (Toyota Axio)">Private Car — Toyota Axio (3 pax)</option>
                                                        <option value="Private Van (Toyota Noah)">Private Van — Toyota Noah (5-6 pax)</option>
                                                        <option value="Private Sedan">Private Sedan (4 pax)</option>
                                                    </optgroup>

                                                    <optgroup label="Premium">
                                                        <option value="Private 4×4 Safari Vehicle">Private 4×4 Safari Vehicle</option>
                                                        <option value="Private Land Cruiser">Private Land Cruiser (7 pax)</option>
                                                        <option value="Private Overland Truck">Private Overland Truck (group)</option>
                                                    </optgroup>

                                                    <optgroup label="Flight">
                                                        <option value="Domestic Flight (Wilson → Nanyuki)">Domestic Flight (Wilson → Nanyuki) ~30 min</option>
                                                        <option value="Private Charter Flight">Private Charter Flight</option>
                                                        <option value="Helicopter Transfer">Helicopter Transfer</option>
                                                    </optgroup>

                                                    <optgroup label="Own Arrangement">
                                                        <option value="Self Drive / Own Vehicle">Self Drive / Own Vehicle</option>
                                                        <option value="Arranged Separately">Arranged Separately</option>
                                                    </optgroup>

                                                    <option value="__custom__">✦ Other (type your own)</option>
                                                </select>

                                                {{-- Custom transport input --}}
                                                <template x-if="pkg.transport_type === '__custom__'">
                                                    <input type="text" x-model="pkg.transport_custom"
                                                           placeholder="Type your transport…"
                                                           class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                </template>
                                            </div>

                                            {{-- ACCOMMODATION DROPDOWN --}}
                                            <div>
                                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Accommodation</label>
                                                <select x-model="pkg.accommodation_type" @change="onAccommodationChange(pi)"
                                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                    <option value="">— Select accommodation —</option>

                                                    <optgroup label="Budget">
                                                        <option value="The Great Circle Lodge (Nanyuki)">The Great Circle Lodge (Nanyuki) — from $46/night</option>
                                                        <option value="Oldmoses Camp">Oldmoses Camp — from $37/night</option>
                                                        <option value="Castle Forest Lodge">Castle Forest Lodge (budget rooms)</option>
                                                        <option value="Mianzi Camp">Mianzi Camp — from $18/night</option>
                                                        <option value="Jim Camp">Jim Camp (campground)</option>
                                                    </optgroup>

                                                    <optgroup label="Mid-range">
                                                        <option value="Aberdare Prestige & Royal Cottages">Aberdare Prestige & Royal Cottages — from $83/night</option>
                                                        <option value="Ole Samara (Nanyuki)">Ole Samara (Nanyuki) — from $83/night</option>
                                                        <option value="The Farmhouse Inn">The Farmhouse Inn</option>
                                                        <option value="Glamping Kenya Mt. Kenya Lodge">Glamping Kenya Mt. Kenya Lodge — from $168/night</option>
                                                        <option value="Falcon Heights Hotel">Falcon Heights Hotel</option>
                                                        <option value="The Glacier Hotel">The Glacier Hotel Nanyuki</option>
                                                        <option value="Timau River Lodge">Timau River Lodge</option>
                                                    </optgroup>

                                                    <optgroup label="Luxury">
                                                        <option value="Fairmont Mount Kenya Safari Club">Fairmont Mount Kenya Safari Club — from $336/night</option>
                                                        <option value="Sweetwaters Serena Camp">Sweetwaters Serena Camp</option>
                                                        <option value="Tufaha Resort">Tufaha Resort</option>
                                                        <option value="Mukima Manor">Mukima Manor</option>
                                                        <option value="Borana Lodge">Borana Lodge (andBeyond)</option>
                                                        <option value="Laragai House">Laragai House</option>
                                                    </optgroup>

                                                    <optgroup label="Premium / Ultra-Luxury">
                                                        <option value="JW Marriott Mount Kenya Rhino Reserve">JW Marriott Mount Kenya Rhino Reserve — from $3,219/night</option>
                                                        <option value="Segera Retreat">Segera Retreat (private ranch)</option>
                                                        <option value="Loisaba Tented Camp">Loisaba Tented Camp</option>
                                                        <option value="Rutundu Log Cabins">Rutundu Log Cabins — from $356/night</option>
                                                    </optgroup>

                                                    <optgroup label="Special">
                                                        <option value="Tented Camp">Tented Camp</option>
                                                        <option value="Mountain Hut">Mountain Hut (Shiptons / Old Moses)</option>
                                                        <option value="Homestay / Airbnb">Homestay / Airbnb</option>
                                                    </optgroup>

                                                    <optgroup label="Own Arrangement">
                                                        <option value="Own Arrangement">Own Arrangement</option>
                                                        <option value="Arranged Separately">Arranged Separately</option>
                                                    </optgroup>

                                                    <option value="__custom__">✦ Other (type your own)</option>
                                                </select>

                                                {{-- Custom accommodation input --}}
                                                <template x-if="pkg.accommodation_type === '__custom__'">
                                                    <input type="text" x-model="pkg.accommodation_custom"
                                                           placeholder="Type your accommodation…"
                                                           class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                </template>
                                            </div>

                                            {{-- SHARING --}}
                                            <div>
                                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Sharing</label>
                                                <select x-model="pkg.accommodation_sharing"
                                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                    <option value="">— Select —</option>
                                                    <option value="Shared">Shared</option>
                                                    <option value="Private">Private</option>
                                                    <option value="Suite">Suite</option>
                                                    <option value="Single">Single room</option>
                                                    <option value="Twin">Twin (2 beds)</option>
                                                    <option value="Double">Double (1 bed)</option>
                                                    <option value="Dormitory">Dormitory</option>
                                                    <option value="Camping">Camping</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Max capacity</label>
                                                <input type="number" x-model.number="pkg.max_capacity"
                                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                            </div>

                                            <div class="sm:col-span-2">
                                                <label class="block text-[11px] font-medium text-gray-500 mb-1">What's included (one per line)</label>
                                                <textarea rows="3"
                                                          :value="(pkg.includes || []).join('\n')"
                                                          @input="pkg.includes = $event.target.value.split('\n').map(s => s.trim()).filter(Boolean)"
                                                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </section>

                        {{-- 3. ITINERARY --}}
                        <section class="rounded-xl border border-gray-200 dark:border-gray-800">
                            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-800">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Itinerary</h3>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Same content everyone sees</p>
                                </div>
                                <button type="button" @click="addDay()"
                                        class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">
                                    + Add Day
                                </button>
                            </div>
                            <div class="p-5 space-y-4">
                                <template x-if="form.days.length === 0">
                                    <p class="text-sm text-gray-400 text-center py-4">No days yet. Click <strong>+ Add Day</strong>.</p>
                                </template>

                                <template x-for="(day, di) in form.days" :key="day.uid">
                                    <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-700 dark:bg-gray-800/30">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-brand-500 text-[11px] font-bold text-white"
                                                      x-text="di + 1"></span>
                                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300"
                                                      x-text="'Day ' + (di + 1) + (day.title ? ' — ' + day.title : '')"></span>
                                            </div>
                                            <button type="button" @click="removeDay(di)"
                                                    class="text-xs text-red-600 hover:underline">Remove Day</button>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                                            <div class="sm:col-span-2">
                                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Title</label>
                                                <input type="text" x-model="day.title" placeholder="Nairobi → Bunda"
                                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Date</label>
                                                <input type="date" x-model="day.date"
                                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Description</label>
                                                <textarea rows="2" x-model="day.description"
                                                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                                            </div>
                                        </div>

                                        <div class="border-t border-gray-200 pt-3 dark:border-gray-700">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">Activities</span>
                                                <button type="button" @click="addActivity(di)"
                                                        class="text-xs text-brand-600 hover:underline dark:text-brand-400">+ Add Activity</button>
                                            </div>

                                            <template x-if="day.activities.length === 0">
                                                <p class="text-xs text-gray-400 italic py-2">No activities yet.</p>
                                            </template>

                                            <div class="space-y-3">
                                                <template x-for="(act, ai) in day.activities" :key="act.uid">
                                                    <div class="rounded-lg bg-white p-3 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">

                                                        <div class="flex items-center justify-between mb-3">
                                                            <span class="text-[11px] font-medium text-gray-500" x-text="'Activity ' + (ai + 1)"></span>
                                                            <button type="button" @click="removeActivity(di, ai)"
                                                                    class="text-xs text-red-600 hover:underline">Remove</button>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="block text-[11px] font-medium text-gray-500 mb-1">Activity Image</label>
                                                            <div class="flex items-start gap-3">
                                                                <div class="relative h-20 w-32 shrink-0 overflow-hidden rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800 flex items-center justify-center">
                                                                    <template x-if="act.image_preview">
                                                                        <img :src="act.image_preview" class="h-full w-full object-cover" alt="">
                                                                    </template>
                                                                    <template x-if="!act.image_preview">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                                             stroke="currentColor" stroke-width="1.5"
                                                                             class="h-6 w-6 text-gray-300 dark:text-gray-600">
                                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                                  d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/>
                                                                        </svg>
                                                                    </template>
                                                                </div>

                                                                <div class="flex-1">
                                                                    <input type="file"
                                                                           accept="image/*"
                                                                           :name="`days[${di}][activities][${ai}][image]`"
                                                                           @change="handleActivityImage($event, di, ai)"
                                                                           class="w-full text-xs text-gray-600 dark:text-gray-300">
                                                                    <p class="mt-1 text-[11px] text-gray-500">JPG / PNG, max 4MB</p>

                                                                    <button type="button"
                                                                            x-show="act.image_preview"
                                                                            @click="clearActivityImage(di, ai)"
                                                                            class="mt-1 text-[11px] font-medium text-red-600 hover:underline">
                                                                        Remove image
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                            <input type="text" x-model="act.title" placeholder="Title"
                                                                   class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                            <input type="text" x-model="act.time_label" placeholder="05:30 AM"
                                                                   class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                            <textarea rows="2" x-model="act.description" placeholder="Description"
                                                                      class="sm:col-span-2 w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                                                            <input type="text"
                                                                   :value="(act.tags || []).join(', ')"
                                                                   @input="act.tags = $event.target.value.split(',').map(s => s.trim()).filter(Boolean)"
                                                                   placeholder="Tags: Scenic, Border Crossing"
                                                                   class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                            <input type="text" x-model="act.pro_tip" placeholder="Pro tip"
                                                                   class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </section>

                        {{-- 4. RACE CATEGORIES --}}
                        <section class="rounded-xl border border-gray-200 dark:border-gray-800">
                            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-800">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Race Categories</h3>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Optional. 42km / 21km / 10km / 5km</p>
                                </div>
                                <button type="button" @click="addCategory()"
                                        class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600">
                                    + Add Category
                                </button>
                            </div>
                            <div class="p-5 space-y-3">
                                <template x-if="form.categories.length === 0">
                                    <p class="text-sm text-gray-400 text-center py-4">No race categories.</p>
                                </template>

                                <template x-for="(cat, ci) in form.categories" :key="cat.uid">
                                    <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-medium text-gray-500 mb-1">Name</label>
                                            <input type="text" x-model="cat.name" placeholder="42km Marathon"
                                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-medium text-gray-500 mb-1">Distance (km)</label>
                                            <input type="number" x-model.number="cat.distance_km"
                                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-medium text-gray-500 mb-1">Surcharge</label>
                                            <input type="number" step="0.01" x-model.number="cat.price_surcharge"
                                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        </div>
                                        <div class="flex items-end gap-2">
                                            <div class="flex-1">
                                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Cap</label>
                                                <input type="number" x-model.number="cat.max_capacity"
                                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                            </div>
                                            <button type="button" @click="removeCategory(ci)"
                                                    class="text-red-600 hover:underline pb-2 text-lg">×</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </section>

                        {{-- 5. IMAGES --}}
                        <section class="rounded-xl border border-gray-200 dark:border-gray-800">
                            <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-800">
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Images</h3>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Leave empty to keep existing.</p>
                            </div>
                            <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Cover</label>
                                    <input type="file" name="image" accept="image/*"
                                           @change="handleImage($event, 'image')"
                                           class="w-full text-xs text-gray-600 dark:text-gray-300">
                                    <div class="mt-2" x-show="form.image_preview">
                                        <img :src="form.image_preview" class="h-20 w-full object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Poster</label>
                                    <input type="file" name="poster_image" accept="image/*"
                                           @change="handleImage($event, 'poster_image')"
                                           class="w-full text-xs text-gray-600 dark:text-gray-300">
                                    <div class="mt-2" x-show="form.poster_image_preview">
                                        <img :src="form.poster_image_preview" class="h-20 w-full object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Banner</label>
                                    <input type="file" name="banner" accept="image/*"
                                           @change="handleImage($event, 'banner')"
                                           class="w-full text-xs text-gray-600 dark:text-gray-300">
                                    <div class="mt-2" x-show="form.banner_preview">
                                        <img :src="form.banner_preview" class="h-20 w-full object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="flex shrink-0 items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                    <button type="button" @click="close()"
                            class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Cancel
                    </button>
                    <button type="submit" :disabled="submitting"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50">
                        <svg x-show="submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity="0.25"/>
                            <path fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        <span x-text="submitting ? 'Saving…' : (mode === 'edit' ? 'Update Experience' : 'Create Experience')"></span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('experienceFormModal', () => ({
        isOpen: false,
        loading: false,
        submitting: false,
        mode: 'create',
        experienceId: null,
        errors: [],

        form: {
            name: '', short_description: '', description: '', location: '',
            start_date: '', end_date: '', total_capacity: '', status: 'active', featured: false,
            image_preview: null, poster_image_preview: null, banner_preview: null,
            packages: [], days: [], categories: [],
        },

        init() {},

        reset() {
            this.form = {
                name: '', short_description: '', description: '', location: '',
                start_date: '', end_date: '', total_capacity: '', status: 'active', featured: false,
                image_preview: null, poster_image_preview: null, banner_preview: null,
                packages: [], days: [], categories: [],
            };
            this.errors = [];
            this.experienceId = null;
            if (this.$root) {
                this.$root.querySelectorAll('input[type="file"]').forEach(i => i.value = '');
            }
        },

        async open(detail) {
            this.reset();
            this.isOpen = true;
            document.body.style.overflow = 'hidden';

            if (detail && detail.id) {
                this.mode = 'edit';
                this.experienceId = detail.id;
                this.loading = true;
                try {
                    const res = await fetch(`/experiences/${detail.id}/json`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const json = await res.json();
                    if (json.success && json.data) {
                        this.hydrate(json.data);
                    } else {
                        this.errors = ['Failed to load experience'];
                    }
                } catch (e) {
                    this.errors = ['Network error: ' + e.message];
                } finally {
                    this.loading = false;
                }
            } else {
                this.mode = 'create';
            }
        },

        hydrate(d) {
            this.form.name                 = d.name || '';
            this.form.short_description    = d.short_description || '';
            this.form.description          = d.description || '';
            this.form.location             = d.location || '';
            this.form.start_date           = d.start_date || '';
            this.form.end_date             = d.end_date || '';
            this.form.total_capacity      = d.total_capacity || '';
            this.form.status               = d.status || 'active';
            this.form.featured             = !!d.featured;
            this.form.image_preview        = d.image_url || null;
            this.form.poster_image_preview = d.poster_image_url || null;
            this.form.banner_preview       = d.banner_url || null;

            this.form.packages = (d.packages || []).map(p => {
                // Detect if the saved value is a known dropdown option or a custom value
                const knownTransports = this._knownTransports();
                const knownAccommodations = this._knownAccommodations();

                const transportIsKnown = !p.transport_type || knownTransports.includes(p.transport_type);
                const accommodationIsKnown = !p.accommodation_type || knownAccommodations.includes(p.accommodation_type);

                return {
                    uid: 'pkg_' + Math.random().toString(36).slice(2),
                    id: p.id || null,
                    name: p.name || '',
                    tier: p.tier || 1,
                    badge_color: p.badge_color || '',
                    tagline: p.tagline || '',
                    price: p.price || 0,
                    currency: p.currency || 'KES',
                    transport_type: transportIsKnown ? (p.transport_type || '') : '__custom__',
                    transport_custom: transportIsKnown ? '' : (p.transport_type || ''),
                    accommodation_type: accommodationIsKnown ? (p.accommodation_type || '') : '__custom__',
                    accommodation_custom: accommodationIsKnown ? '' : (p.accommodation_type || ''),
                    accommodation_sharing: p.accommodation_sharing || '',
                    max_capacity: p.max_capacity || '',
                    includes: Array.isArray(p.includes) ? p.includes : [],
                };
            });

            this.form.days = (d.days || []).map(day => ({
                uid: 'day_' + Math.random().toString(36).slice(2),
                id: day.id || null,
                title: day.title || '',
                date: day.date || '',
                description: day.description || '',
                activities: (day.activities || []).map(a => ({
                    uid: 'act_' + Math.random().toString(36).slice(2),
                    id: a.id || null,
                    title: a.title || '',
                    time_label: a.time_label || '',
                    description: a.description || '',
                    tags: Array.isArray(a.tags) ? a.tags : [],
                    pro_tip: a.pro_tip || '',
                    image_preview: a.image_url || null,
                })),
            }));

            this.form.categories = (d.categories || []).map(c => ({
                uid: 'cat_' + Math.random().toString(36).slice(2),
                id: c.id || null,
                name: c.name || '',
                distance_km: c.distance_km || '',
                price_surcharge: c.price_surcharge || 0,
                max_capacity: c.max_capacity || '',
            }));
        },

        // List of known transport values (must match the <option> values exactly)
        _knownTransports() {
            return [
                'Shared Matatu / Bus (Nairobi → Nanyuki)',
                'Shared Shuttle Van',
                'Public Bus (Nyamakima)',
                'Private Car (Toyota Axio)',
                'Private Van (Toyota Noah)',
                'Private Sedan',
                'Private 4×4 Safari Vehicle',
                'Private Land Cruiser',
                'Private Overland Truck',
                'Domestic Flight (Wilson → Nanyuki)',
                'Private Charter Flight',
                'Helicopter Transfer',
                'Self Drive / Own Vehicle',
                'Arranged Separately',
            ];
        },

        // List of known accommodation values (must match the <option> values exactly)
        _knownAccommodations() {
            return [
                'The Great Circle Lodge (Nanyuki)',
                'Oldmoses Camp',
                'Castle Forest Lodge',
                'Mianzi Camp',
                'Jim Camp',
                'Aberdare Prestige & Royal Cottages',
                'Ole Samara (Nanyuki)',
                'The Farmhouse Inn',
                'Glamping Kenya Mt. Kenya Lodge',
                'Falcon Heights Hotel',
                'The Glacier Hotel',
                'Timau River Lodge',
                'Fairmont Mount Kenya Safari Club',
                'Sweetwaters Serena Camp',
                'Tufaha Resort',
                'Mukima Manor',
                'Borana Lodge',
                'Laragai House',
                'JW Marriott Mount Kenya Rhino Reserve',
                'Segera Retreat',
                'Loisaba Tented Camp',
                'Rutundu Log Cabins',
                'Tented Camp',
                'Mountain Hut',
                'Homestay / Airbnb',
                'Own Arrangement',
                'Arranged Separately',
            ];
        },

        close() {
            this.isOpen = false;
            document.body.style.overflow = '';
            setTimeout(() => this.reset(), 300);
        },

        addPackage() {
            this.form.packages.push({
                uid: 'pkg_' + Math.random().toString(36).slice(2),
                id: null, name: '', tier: this.form.packages.length + 1, badge_color: '',
                tagline: '', price: 0, currency: 'KES',
                transport_type: '', transport_custom: '',
                accommodation_type: '', accommodation_custom: '',
                accommodation_sharing: '',
                max_capacity: '', includes: [],
            });
        },
        removePackage(i) { if (confirm('Remove this package?')) this.form.packages.splice(i, 1); },

        onPackageNameChange(i) {
            const map = {
                'Bronze':   { tier: 1, color: '#CD7F32' },
                'Silver':   { tier: 2, color: '#A8A9AD' },
                'Gold':     { tier: 3, color: '#D4AF37' },
                'Platinum': { tier: 4, color: '#E5E4E2' },
            };
            const pkg = this.form.packages[i];
            const hit = map[pkg.name];
            if (hit) { pkg.tier = hit.tier; pkg.badge_color = hit.color; }
        },

        onTransportChange(i) {
            const pkg = this.form.packages[i];
            if (pkg.transport_type !== '__custom__') {
                pkg.transport_custom = '';
            }
        },

        onAccommodationChange(i) {
            const pkg = this.form.packages[i];
            if (pkg.accommodation_type !== '__custom__') {
                pkg.accommodation_custom = '';
            }
        },

        // Resolve the actual value to send to the server
        resolveTransport(pkg) {
            if (pkg.transport_type === '__custom__') {
                return (pkg.transport_custom || '').trim();
            }
            return pkg.transport_type || '';
        },

        resolveAccommodation(pkg) {
            if (pkg.accommodation_type === '__custom__') {
                return (pkg.accommodation_custom || '').trim();
            }
            return pkg.accommodation_type || '';
        },

        addDay() {
            this.form.days.push({
                uid: 'day_' + Math.random().toString(36).slice(2),
                id: null, title: '', date: '', description: '', activities: [],
            });
        },
        removeDay(i) { if (confirm('Remove Day ' + (i + 1) + '?')) this.form.days.splice(i, 1); },

        addActivity(di) {
            this.form.days[di].activities.push({
                uid: 'act_' + Math.random().toString(36).slice(2),
                id: null, title: '', time_label: '', description: '',
                tags: [], pro_tip: '', image_preview: null,
            });
        },
        removeActivity(di, ai) { this.form.days[di].activities.splice(ai, 1); },

        handleActivityImage(e, di, ai) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = ev => {
                this.form.days[di].activities[ai].image_preview = ev.target.result;
            };
            reader.readAsDataURL(file);
        },
        clearActivityImage(di, ai) {
            const act = this.form.days[di].activities[ai];
            act.image_preview = null;
            const input = this.$root.querySelector(`input[type="file"][name="days[${di}][activities][${ai}][image]"]`);
            if (input) input.value = '';
        },

        addCategory() {
            this.form.categories.push({
                uid: 'cat_' + Math.random().toString(36).slice(2),
                id: null, name: '', distance_km: '', price_surcharge: 0, max_capacity: '',
            });
        },
        removeCategory(i) { this.form.categories.splice(i, 1); },

        handleImage(e, field) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = ev => { this.form[field + '_preview'] = ev.target.result; };
            reader.readAsDataURL(file);
        },

        async submit() {
            this.errors = [];
            this.submitting = true;

            try {
                const fd = new FormData();
                fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                if (this.mode === 'edit') fd.append('_method', 'PUT');

                fd.append('name', this.form.name || '');
                fd.append('short_description', this.form.short_description || '');
                fd.append('description', this.form.description || '');
                fd.append('location', this.form.location || '');
                fd.append('start_date', this.form.start_date || '');
                fd.append('end_date', this.form.end_date || '');
                fd.append('total_capacity', this.form.total_capacity || '');
                fd.append('status', this.form.status || 'active');
                fd.append('featured', this.form.featured ? '1' : '0');

                const formEl = this.$root.querySelector('form');

                ['image', 'poster_image', 'banner'].forEach(name => {
                    const inp = formEl.querySelector(`input[type="file"][name="${name}"]`);
                    if (inp && inp.files[0]) fd.append(name, inp.files[0]);
                });

                // Packages — uses resolveTransport / resolveAccommodation for custom values
                this.form.packages.forEach((p, i) => {
                    if (p.id) fd.append(`packages[${i}][id]`, p.id);
                    fd.append(`packages[${i}][name]`, p.name || '');
                    fd.append(`packages[${i}][tier]`, p.tier || (i + 1));
                    fd.append(`packages[${i}][price]`, p.price || 0);
                    fd.append(`packages[${i}][currency]`, p.currency || 'KES');
                    fd.append(`packages[${i}][badge_color]`, p.badge_color || '');
                    fd.append(`packages[${i}][tagline]`, p.tagline || '');
                    fd.append(`packages[${i}][transport_type]`, this.resolveTransport(p));
                    fd.append(`packages[${i}][accommodation_type]`, this.resolveAccommodation(p));
                    fd.append(`packages[${i}][accommodation_sharing]`, p.accommodation_sharing || '');
                    fd.append(`packages[${i}][max_capacity]`, p.max_capacity || '');
                    (p.includes || []).forEach(inc => fd.append(`packages[${i}][includes][]`, inc));
                });

                // Days + activities
                this.form.days.forEach((day, di) => {
                    if (day.id) fd.append(`days[${di}][id]`, day.id);
                    fd.append(`days[${di}][day_number]`, di + 1);
                    fd.append(`days[${di}][title]`, day.title || '');
                    fd.append(`days[${di}][date]`, day.date || '');
                    fd.append(`days[${di}][description]`, day.description || '');

                    (day.activities || []).forEach((act, ai) => {
                        if (act.id) fd.append(`days[${di}][activities][${ai}][id]`, act.id);
                        fd.append(`days[${di}][activities][${ai}][title]`, act.title || '');
                        fd.append(`days[${di}][activities][${ai}][time_label]`, act.time_label || '');
                        fd.append(`days[${di}][activities][${ai}][description]`, act.description || '');
                        fd.append(`days[${di}][activities][${ai}][pro_tip]`, act.pro_tip || '');
                        (act.tags || []).forEach(t => fd.append(`days[${di}][activities][${ai}][tags][]`, t));

                        const fileKey = `days[${di}][activities][${ai}][image]`;
                        const inp = formEl.querySelector(`input[type="file"][name="${fileKey}"]`);
                        if (inp && inp.files[0]) fd.append(fileKey, inp.files[0]);
                    });
                });

                // Categories
                this.form.categories.forEach((c, i) => {
                    if (c.id) fd.append(`categories[${i}][id]`, c.id);
                    fd.append(`categories[${i}][name]`, c.name || '');
                    fd.append(`categories[${i}][distance_km]`, c.distance_km || '');
                    fd.append(`categories[${i}][price_surcharge]`, c.price_surcharge || 0);
                    fd.append(`categories[${i}][max_capacity]`, c.max_capacity || '');
                });

                const url = this.mode === 'edit'
                    ? `/experiences/${this.experienceId}`
                    : `/experiences`;

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: fd,
                });

                const json = await res.json();

                if (json.success) {
                    this.close();
                    window.showToast && window.showToast(json.message || 'Saved');
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    this.errors = [];
                    if (json.errors) {
                        Object.values(json.errors).flat().forEach(m => this.errors.push(m));
                    } else {
                        this.errors = [json.message || 'Something went wrong'];
                    }
                    const body = this.$root.querySelector('.overflow-y-auto');
                    if (body) body.scrollTo({ top: 0 });
                }
            } catch (err) {
                console.error('[Modal] submit error', err);
                this.errors = ['Network error: ' + err.message];
            } finally {
                this.submitting = false;
            }
        },
    }));
});
</script>