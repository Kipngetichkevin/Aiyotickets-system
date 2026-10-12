{{-- resources/views/experiences/index.blade.php --}}
@extends('layouts.app')

@section('content')
<main>
    <div x-data="{ pageName: 'Experiences' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">

        {{-- HEADER --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Experiences</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Multi-day adventures with packages, itineraries and race categories.
                </p>
            </div>

            <button type="button"
                    onclick="window.dispatchEvent(new CustomEvent('open-experience-modal'))"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" class="h-4 w-4">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                New Experience
            </button>
        </div>

        {{-- STAT CARDS --}}
        @php
            $statCards = [
                ['label' => 'Total',    'value' => $stats['total'],    'color' => 'text-gray-800 dark:text-white/90',   'accent' => 'bg-gray-300 dark:bg-gray-600'],
                ['label' => 'Active',   'value' => $stats['active'],   'color' => 'text-green-700 dark:text-green-400', 'accent' => 'bg-green-500'],
                ['label' => 'Draft',    'value' => $stats['draft'],    'color' => 'text-yellow-700 dark:text-yellow-400','accent' => 'bg-yellow-400'],
                ['label' => 'Featured', 'value' => $stats['featured'], 'color' => 'text-purple-700 dark:text-purple-400','accent' => 'bg-purple-500'],
            ];
        @endphp

        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach($statCards as $card)
                <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                    <span class="absolute inset-y-0 left-0 w-1 {{ $card['accent'] }}"></span>
                    <p class="pl-2 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ $card['label'] }}
                    </p>
                    <p class="pl-2 mt-1.5 text-2xl font-bold leading-none {{ $card['color'] }}">
                        {{ $card['value'] }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- FILTERS --}}
        <form method="GET" class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                    <select name="status"
                            class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">All</option>
                        <option value="active"   @selected(request('status') === 'active')>Active</option>
                        <option value="draft"    @selected(request('status') === 'draft')>Draft</option>
                        <option value="archived" @selected(request('status') === 'archived')>Archived</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1 grow min-w-[220px]">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Search</label>
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Search by name, location, description..."
                           class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                </div>

                <button type="submit" class="h-10 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600">
                    Filter
                </button>

                @if(request()->anyFilled(['status','q']))
                    <a href="{{ route('experiences.index') }}"
                       class="h-10 inline-flex items-center px-2 text-sm text-gray-500 hover:text-gray-800 dark:text-gray-400">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        {{-- GRID --}}
        @if($experiences->count() > 0)
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($experiences as $exp)
                    <div class="group rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 transition hover:shadow-theme-md hover:border-blue-200 dark:hover:border-blue-800 overflow-hidden">
                        <div class="aspect-video overflow-hidden bg-gray-100 dark:bg-gray-800 relative">
                            @if($exp->image)
                                <img src="{{ asset('storage/' . $exp->image) }}" alt="{{ $exp->name }}"
                                     class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                            @elseif($exp->poster_image)
                                <img src="{{ asset('storage/' . $exp->poster_image) }}" alt="{{ $exp->name }}"
                                     class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="1.5" class="h-16 w-16">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/>
                                    </svg>
                                </div>
                            @endif

                            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                                @if($exp->status === 'active')
                                    <span class="inline-flex items-center rounded-full bg-green-500/90 px-2.5 py-1 text-xs font-semibold text-white shadow">Active</span>
                                @elseif($exp->status === 'draft')
                                    <span class="inline-flex items-center rounded-full bg-yellow-500/90 px-2.5 py-1 text-xs font-semibold text-white shadow">Draft</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-500/90 px-2.5 py-1 text-xs font-semibold text-white shadow">Archived</span>
                                @endif

                                @if($exp->featured)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-purple-500/90 px-2.5 py-1 text-xs font-semibold text-white shadow">★ Featured</span>
                                @endif
                            </div>

                            {{-- Bookings pill (top-right) --}}
                            <a href="{{ route('experiences.bookings', $exp->id) }}"
                               class="absolute top-3 right-3 inline-flex items-center gap-1.5 rounded-full bg-black/60 px-2.5 py-1 text-xs font-semibold text-white shadow backdrop-blur-sm hover:bg-black/80 transition"
                               title="View bookings">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/>
                                </svg>
                                {{ $exp->bookings_count ?? 0 }}
                            </a>
                        </div>

                        <div class="p-5">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                                {{ $exp->name }}
                            </h3>

                            @if($exp->short_description)
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $exp->short_description }}</p>
                            @endif

                            <div class="mt-3 space-y-1.5 text-xs text-gray-500 dark:text-gray-400">
                                @if($exp->location)
                                    <p>📍 {{ $exp->location }}</p>
                                @endif
                                @if($exp->start_date)
                                    <p>
                                        📅 {{ $exp->start_date->format('d M Y') }}
                                        @if($exp->end_date) – {{ $exp->end_date->format('d M Y') }} @endif
                                    </p>
                                @endif
                                <div class="flex items-center gap-3 pt-1">
                                    <span class="font-medium text-brand-600 dark:text-brand-400">
                                        📦 {{ $exp->packages->count() }} package{{ $exp->packages->count() === 1 ? '' : 's' }}
                                    </span>
                                    @if($exp->categories->count() > 0)
                                        <span>🏃 {{ $exp->categories->count() }} categor{{ $exp->categories->count() === 1 ? 'y' : 'ies' }}</span>
                                    @endif
                                </div>
                            </div>

                            @if($exp->packages->count() > 0)
                                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Starting from</p>
                                    <p class="text-lg font-bold text-gray-800 dark:text-white/90">
                                        KES {{ number_format((float) $exp->packages->min('price'), 0) }}
                                    </p>
                                </div>
                            @endif

                            <div class="mt-4 flex items-center justify-between gap-2">
                                <a href="{{ route('experience.show', $exp->slug) }}" target="_blank"
                                   class="text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">
                                    View Public →
                                </a>

                                <div class="flex items-center gap-1">
                                    <a href="{{ route('experiences.bookings', $exp->id) }}"
                                       title="Bookings"
                                       class="rounded-md p-2 text-gray-400 hover:bg-purple-50 hover:text-purple-700 dark:hover:bg-purple-500/10">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                                        </svg>
                                    </a>

                                    <button type="button" onclick="toggleExperienceStatus({{ $exp->id }})"
                                            title="Toggle status"
                                            class="rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                        </svg>
                                    </button>

                                    <button type="button" onclick="openEdit({{ $exp->id }})"
                                            title="Edit"
                                            class="rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                        </svg>
                                    </button>

                                    <button type="button" onclick="deleteExperience({{ $exp->id }}, '{{ addslashes($exp->name) }}')"
                                            title="Delete"
                                            class="rounded-md p-2 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($experiences->hasPages())
                <div class="mt-6">{{ $experiences->links() }}</div>
            @endif
        @else
            <div class="rounded-xl border-2 border-dashed border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                     class="mx-auto mb-4 h-16 w-16 text-gray-300 dark:text-gray-600">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">No experiences yet</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    Create your first experience — a multi-day adventure with packages and an itinerary.
                </p>
                <button type="button"
                        onclick="window.dispatchEvent(new CustomEvent('open-experience-modal'))"
                        class="mt-5 inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Create Your First Experience
                </button>
            </div>
        @endif

    </div>

    {{-- DELETE MODAL --}}
    <div id="delete-modal" style="display:none;" class="fixed inset-0 z-[99999] bg-black/50 p-4">
        <div class="mx-auto mt-40 w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Delete Experience?</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Are you sure you want to delete <strong id="delete-name"></strong>?
                This will remove all packages, days, activities and bookings. Cannot be undone.
            </p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" onclick="closeDeleteModal()"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                    Cancel
                </button>
                <button type="button" id="btn-confirm-delete"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    Delete
                </button>
            </div>
        </div>
    </div>

    {{-- TOAST --}}
    <div id="toast" style="display:none;"
         class="fixed bottom-6 right-6 z-[100000] rounded-lg px-4 py-3 text-sm font-medium text-white shadow-lg"></div>

    {{-- THE FORM MODAL --}}
    @include('experiences._form-modal')

</main>
@endsection

<script>
window.openEdit = function (id) {
    window.dispatchEvent(new CustomEvent('open-experience-modal', { detail: { id: id } }));
};

let pendingDeleteId = null;

window.deleteExperience = function (id, name) {
    pendingDeleteId = id;
    document.getElementById('delete-name').textContent = name;
    document.getElementById('delete-modal').style.display = 'block';
};

window.closeDeleteModal = function () {
    pendingDeleteId = null;
    document.getElementById('delete-modal').style.display = 'none';
};

document.getElementById('btn-confirm-delete').addEventListener('click', async function () {
    if (!pendingDeleteId) return;
    try {
        const res = await fetch(`/experiences/${pendingDeleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });
        const json = await res.json();
        if (json.success) {
            showToast('Deleted');
            closeDeleteModal();
            setTimeout(() => window.location.reload(), 500);
        } else {
            showToast(json.message || 'Failed', 'error');
        }
    } catch (err) {
        showToast('Network error', 'error');
    }
});

window.toggleExperienceStatus = async function (id) {
    try {
        const res = await fetch(`/experiences/${id}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });
        const json = await res.json();
        if (json.success) {
            showToast('Status updated');
            setTimeout(() => window.location.reload(), 400);
        } else {
            showToast(json.message || 'Failed', 'error');
        }
    } catch (err) {
        showToast('Network error', 'error');
    }
};

window.showToast = function (m, type = 'success') {
    const el = document.getElementById('toast');
    el.textContent = m;
    el.className = 'fixed bottom-6 right-6 z-[100000] rounded-lg px-4 py-3 text-sm font-medium text-white shadow-lg ' +
        (type === 'error' ? 'bg-red-600' : 'bg-green-600');
    el.style.display = 'block';
    clearTimeout(el._t);
    el._t = setTimeout(() => el.style.display = 'none', 3500);
};
</script>