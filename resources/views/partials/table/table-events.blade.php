{{-- resources/views/partials/table/table-events.blade.php --}}
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

    @if($events->isEmpty())
        {{-- Empty state --}}
        <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-500/10 mb-4">
                <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">No events yet</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-sm">
                Once you create your first event, it'll show up here with all its stats.
            </p>
            <a href="{{ route('events.create') }}"
               class="mt-5 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create your first event
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Event</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 hidden md:table-cell">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 hidden lg:table-cell">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 hidden lg:table-cell">Location</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($events as $event)
                        @php
                            $statusStyles = [
                                'active'    => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-400',
                                'inactive'  => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-400',
                                'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-400',
                                'completed' => 'bg-gray-100 text-gray-800 dark:bg-gray-500/10 dark:text-gray-400',
                            ];
                            $badgeClass = $statusStyles[$event->status] ?? $statusStyles['completed'];

                            $isUpcoming = $event->date && $event->date->isFuture();
                            $daysUntil  = $isUpcoming ? now()->diffInDays($event->date) : null;
                        @endphp

                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                            {{-- Event cell --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                                        @if($event->image)
                                            <img src="{{ $event->image }}" alt=""
                                                 class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-gray-400">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('events.show', $event->id) }}"
                                           class="block truncate text-sm font-medium text-gray-800 hover:text-blue-600 dark:text-white/90">
                                            {{ $event->name }}
                                        </a>
                                        @if($event->merchant)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                by {{ $event->merchant->name }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Type --}}
                            <td class="px-4 py-3 hidden md:table-cell">
                                @if($event->eventType)
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                                        {{ $event->eventType->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <div class="text-sm text-gray-800 dark:text-white/90">
                                    {{ $event->date ? $event->date->format('d M Y') : '—' }}
                                </div>
                                @if($daysUntil !== null && $daysUntil <= 7)
                                    <p class="text-xs text-orange-600 dark:text-orange-400 font-medium">
                                        {{ $daysUntil == 0 ? 'Today' : ($daysUntil == 1 ? 'Tomorrow' : "In {$daysUntil} days") }}
                                    </p>
                                @endif
                            </td>

                            {{-- Location --}}
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <span class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-[200px] inline-block">
                                    {{ $event->location ?: '—' }}
                                </span>
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                    {{ ucfirst($event->status) }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('events.show', $event->id) }}"
                                       title="View"
                                       class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-blue-600 dark:hover:bg-white/[0.05]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('events.edit', $event->id) }}"
                                       title="Edit"
                                       class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-blue-600 dark:hover:bg-white/[0.05]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <button type="button"
                                            title="Delete"
                                            onclick="if(confirm('Delete “{{ addslashes($event->name) }}”? This cannot be undone.')) { document.getElementById('delete-event-{{ $event->id }}').submit(); }"
                                            class="rounded-lg p-2 text-gray-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                                        </svg>
                                    </button>
                                    <form id="delete-event-{{ $event->id }}"
                                          action="{{ route('events.destroy', $event->id) }}"
                                          method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>