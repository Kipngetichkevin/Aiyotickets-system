@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6" x-data="{ tab: '{{ request('tab', 'general') }}' }">

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">System Settings</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Manage application-wide configuration
            </p>
        </div>
        <a href="{{ route('system.clear-cache') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Clear Cache
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
            <p class="font-medium mb-1">Please fix the following:</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Tabs --}}
    <div class="mb-6 flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-800">
        @foreach([
            'general'    => 'General',
            'branding'   => 'Branding',
            'regional'   => 'Regional',
            'appearance' => 'Appearance',
            'system'     => 'System',
        ] as $key => $label)
            <button type="button"
                @click="tab = '{{ $key }}'"
                :class="tab === '{{ $key }}' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                class="px-4 py-2 -mb-px text-sm font-medium border-b-2 transition-colors">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <form method="POST" action="{{ route('system.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- LEFT: content per tab --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- ==================== GENERAL ==================== --}}
                <div x-show="tab === 'general'" x-cloak class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Application Information</h3>
                    </div>
                    <div class="space-y-5 p-5 sm:p-6">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Application Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $system->name) }}" required
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-blue-300 focus:ring-3 focus:ring-blue-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Slogan</label>
                            <input type="text" name="slogan" value="{{ old('slogan', $system->slogan) }}"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-blue-300 focus:ring-3 focus:ring-blue-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>

                        <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Contact</h4>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email</label>
                                    <input type="email" name="contact_email" value="{{ old('contact_email', $system->contact_email) }}"
                                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-blue-300 focus:ring-3 focus:ring-blue-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Phone</label>
                                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $system->contact_phone) }}"
                                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-blue-300 focus:ring-3 focus:ring-blue-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Address</label>
                                    <textarea name="address" rows="2"
                                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-blue-300 focus:ring-3 focus:ring-blue-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('address', $system->address) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pagination Limit <span class="text-red-500">*</span></label>
                            <input type="number" name="pagination_limit" value="{{ old('pagination_limit', $system->pagination_limit) }}" min="5" max="100" required
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-blue-300 focus:ring-3 focus:ring-blue-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Items per page in admin tables (5–100)</p>
                        </div>
                    </div>
                </div>

                {{-- ==================== BRANDING ==================== --}}
                <div x-show="tab === 'branding'" x-cloak class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Logos & Icons</h3>
                    </div>
                    <div class="space-y-5 p-5 sm:p-6">
                        @foreach([
                            'logo'      => ['label' => 'Main Logo (Light)', 'hint' => 'Used in the sidebar and PDF tickets. 200×60 px PNG/SVG.', 'bg' => 'bg-white'],
                            'logo_dark' => ['label' => 'Dark Logo',         'hint' => 'Shown in dark mode. 200×60 px.',                        'bg' => 'bg-gray-800'],
                            'logo_icon' => ['label' => 'Logo Icon',         'hint' => 'Square icon for collapsed sidebar. 64×64 px.',          'bg' => 'bg-white'],
                            'favicon'   => ['label' => 'Favicon',           'hint' => 'Browser tab icon. 32×32 px ICO or PNG.',                'bg' => 'bg-white'],
                        ] as $field => $meta)
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ $meta['label'] }}</label>
                                <div class="flex items-center gap-4">
                                    @if($system->$field)
                                        <div class="flex h-16 w-16 items-center justify-center rounded-lg border border-gray-200 {{ $meta['bg'] }} p-2 dark:border-gray-700">
                                            <img src="{{ asset('storage/' . $system->$field) }}" alt="{{ $meta['label'] }}" class="max-h-full max-w-full">
                                        </div>
                                    @else
                                        <div class="flex h-16 w-16 items-center justify-center rounded-lg border border-dashed border-gray-300 dark:border-gray-700">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <input type="file" name="{{ $field }}" accept="image/*"
                                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 file:mr-3 file:rounded file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-xs file:font-medium dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:file:bg-gray-800 dark:file:text-white">
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $meta['hint'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ==================== REGIONAL ==================== --}}
                <div x-show="tab === 'regional'" x-cloak class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Regional Settings</h3>
                    </div>
                    <div class="space-y-5 p-5 sm:p-6">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Timezone <span class="text-red-500">*</span></label>
                            <select name="timezone" required
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-blue-300 focus:ring-3 focus:ring-blue-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                @foreach($timezones as $tz)
                                    <option value="{{ $tz }}" @selected(old('timezone', $system->timezone) == $tz)>{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Date Format <span class="text-red-500">*</span></label>
                                <select name="date_format" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-blue-300 focus:ring-3 focus:ring-blue-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    @foreach([
                                        'd-m-Y'  => 'DD-MM-YYYY',
                                        'd/m/Y'  => 'DD/MM/YYYY',
                                        'm/d/Y'  => 'MM/DD/YYYY',
                                        'Y-m-d'  => 'YYYY-MM-DD',
                                        'd M, Y' => 'DD Mon, YYYY',
                                    ] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('date_format', $system->date_format) == $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Time Format <span class="text-red-500">*</span></label>
                                <select name="time_format" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-blue-300 focus:ring-3 focus:ring-blue-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    @foreach([
                                        'H:i:s'   => '24-hour (HH:MM:SS)',
                                        'H:i'     => '24-hour (HH:MM)',
                                        'h:i:s A' => '12-hour (HH:MM:SS AM/PM)',
                                        'h:i A'   => '12-hour (HH:MM AM/PM)',
                                    ] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('time_format', $system->time_format) == $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Currency <span class="text-red-500">*</span></label>
                                <select name="currency" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-blue-300 focus:ring-3 focus:ring-blue-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    @foreach($currencies as $code => $label)
                                        <option value="{{ $code }}" @selected(old('currency', $system->currency) == $code)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Currency Symbol <span class="text-red-500">*</span></label>
                                <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $system->currency_symbol) }}" required
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-blue-300 focus:ring-3 focus:ring-blue-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ==================== APPEARANCE ==================== --}}
                <div x-show="tab === 'appearance'" x-cloak class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Appearance & Theme</h3>
                    </div>
                    <div class="space-y-5 p-5 sm:p-6">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Primary Color <span class="text-red-500">*</span></label>
                                <div class="flex items-center gap-3">
                                    <input type="color" name="primary_color" value="{{ old('primary_color', $system->primary_color) }}" required
                                        class="h-11 w-16 cursor-pointer rounded-lg border border-gray-300 dark:border-gray-700">
                                    <span class="flex-1 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-mono text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                        {{ $system->primary_color }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Secondary Color <span class="text-red-500">*</span></label>
                                <div class="flex items-center gap-3">
                                    <input type="color" name="secondary_color" value="{{ old('secondary_color', $system->secondary_color) }}" required
                                        class="h-11 w-16 cursor-pointer rounded-lg border border-gray-300 dark:border-gray-700">
                                    <span class="flex-1 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-mono text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                        {{ $system->secondary_color }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Custom CSS</label>
                            <textarea name="custom_css" rows="6" placeholder="/* Your custom styles */"
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 font-mono text-xs text-gray-800 focus:border-blue-300 focus:ring-3 focus:ring-blue-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('custom_css', $system->custom_css) }}</textarea>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Custom JavaScript</label>
                            <textarea name="custom_js" rows="6" placeholder="// Your custom scripts"
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 font-mono text-xs text-gray-800 focus:border-blue-300 focus:ring-3 focus:ring-blue-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('custom_js', $system->custom_js) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ==================== SYSTEM ==================== --}}
                <div x-show="tab === 'system'" x-cloak class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">System</h3>
                    </div>
                    <div class="p-5 sm:p-6 space-y-6">

                        {{-- Maintenance toggle --}}
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-900/50">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h4 class="font-medium text-gray-800 dark:text-white/90">Maintenance Mode</h4>
                                    <p class="text-sm mt-1">
                                        @if($system->maintenance_mode)
                                            <span class="inline-flex items-center gap-1 text-yellow-600">
                                                <span class="h-2 w-2 rounded-full bg-yellow-500"></span> Currently ON
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-green-600">
                                                <span class="h-2 w-2 rounded-full bg-green-500"></span> Currently OFF
                                            </span>
                                        @endif
                                    </p>
                                </div>
                                <button type="button"
                                    onclick="if(confirm('Toggle maintenance mode?')){ document.getElementById('maintenance-form').submit(); }"
                                    class="rounded-lg bg-yellow-500 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-600">
                                    {{ $system->maintenance_mode ? 'Disable' : 'Enable' }}
                                </button>
                            </div>
                        </div>

                        {{-- Environment info --}}
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                            <h4 class="font-medium text-gray-800 dark:text-white/90 mb-3">Environment</h4>
                            <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-sm">
                                <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 py-2">
                                    <dt class="text-gray-500 dark:text-gray-400">PHP Version</dt>
                                    <dd class="font-mono text-gray-800 dark:text-white/90">{{ phpversion() }}</dd>
                                </div>
                                <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 py-2">
                                    <dt class="text-gray-500 dark:text-gray-400">Laravel</dt>
                                    <dd class="font-mono text-gray-800 dark:text-white/90">{{ app()->version() }}</dd>
                                </div>
                                <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 py-2">
                                    <dt class="text-gray-500 dark:text-gray-400">Environment</dt>
                                    <dd class="font-mono text-gray-800 dark:text-white/90">{{ config('app.env') }}</dd>
                                </div>
                                <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 py-2">
                                    <dt class="text-gray-500 dark:text-gray-400">Debug</dt>
                                    <dd class="font-mono {{ config('app.debug') ? 'text-yellow-600' : 'text-green-600' }}">
                                        {{ config('app.debug') ? 'On' : 'Off' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: current values + actions --}}
            <div class="space-y-6">

                {{-- Current setup --}}
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Current Setup</h3>
                    </div>
                    <div class="p-5 sm:p-6 space-y-4 text-sm">
                        @if($system->logo)
                            <div class="flex items-center justify-center pb-4 border-b border-gray-100 dark:border-gray-800">
                                <img src="{{ asset('storage/' . $system->logo) }}" alt="{{ $system->name }}" class="h-10 w-auto">
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">App</span>
                            <span class="font-medium text-gray-800 dark:text-white/90">{{ $system->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Currency</span>
                            <span class="font-medium text-gray-800 dark:text-white/90">{{ $system->currency }} ({{ $system->currency_symbol }})</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Timezone</span>
                            <span class="font-medium text-gray-800 dark:text-white/90 text-right">{{ $system->timezone }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Maintenance</span>
                            @if($system->maintenance_mode)
                                <span class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700">ON</span>
                            @else
                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">OFF</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Save --}}
                <div class="flex flex-col gap-2">
                    <button type="submit"
                        class="w-full rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white transition-colors hover:bg-blue-700">
                        Save Changes
                    </button>
                    <button type="reset"
                        class="w-full rounded-lg border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Reset Form
                    </button>
                </div>
            </div>

        </div>
    </form>

    {{-- Maintenance toggle form (separate — not nested inside settings form) --}}
    <form id="maintenance-form" method="POST" action="{{ route('system.toggle-maintenance') }}" class="hidden">
        @csrf
    </form>
</div>

<style>[x-cloak] { display: none !important; }</style>
@endsection