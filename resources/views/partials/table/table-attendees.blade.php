{{-- resources/views/partials/table/table-attendees.blade.php --}}
@props(['attendees', 'event'])

<div class="bg-white rounded-xl shadow-sm p-6 mx-4 dark:bg-neutral-800 dark:border-neutral-700">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Attendees</h2>
            <p class="text-sm text-gray-600 dark:text-neutral-400">Manage event attendees</p>
        </div>
        <div class="flex gap-2">
            <a href="#" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 dark:bg-transparent dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Export CSV
            </a>
            <button class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700">
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/><path d="M12 5v14"/>
                </svg>
                Add Attendee
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-800">
                <tr>
                    <th scope="col" class="ps-6 py-3 text-start">
                        <label for="hs-at-with-checkboxes-main" class="flex">
                            <input type="checkbox" class="shrink-0 border-gray-300 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-600 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800" id="hs-at-with-checkboxes-main">
                            <span class="sr-only"></span>
                        </label>
                    </th>
                    <th scope="col" class="ps-6 lg:ps-3 xl:ps-0 pe-6 py-3 text-start">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Name</span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-start">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Details</span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-start">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Status</span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-start">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Order No.</span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-start">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Total</span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-end">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Action</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                @php
                    $hasAttendees = false;
                @endphp
                @if(isset($attendees) && $attendees->count() > 0)
                    @foreach($attendees as $attendee)
                        @php $hasAttendees = true; @endphp
                        <tr>
                            <td class="size-px whitespace-nowrap">
                                <div class="ps-6 py-3">
                                    <input type="checkbox" class="shrink-0 border-gray-300 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-600 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                                </div>
                            </td>
                            <td class="size-px whitespace-nowrap">
                                <div class="ps-6 lg:ps-3 xl:ps-0 pe-6 py-3">
                                    <div class="flex items-center gap-x-3">
                                        <img class="inline-block size-[38px] rounded-full" 
                                             src="https://ui-avatars.com/api/?name={{ urlencode($attendee->name) }}&background=random" 
                                             alt="Avatar">
                                        <div class="grow">
                                            <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ $attendee->name }}</span>
                                            <span class="block text-sm text-gray-500 dark:text-neutral-500">Code: {{ $attendee->transaction_code ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="h-px w-72 whitespace-nowrap">
                                <div class="px-6 py-3">
                                    <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ $attendee->email ?? 'N/A' }}</span>
                                    <span class="block text-sm text-gray-500 dark:text-neutral-500">{{ $attendee->phone ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="size-px whitespace-nowrap">
                                @if($attendee->qr_code == 1)
                                    <div class="px-6 py-3">
                                        <span class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs font-medium bg-red-100 text-red-800 rounded-full dark:bg-red-500/10 dark:text-red-500">
                                            <svg class="size-2.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.146-2.854a.5.5 0 1 0-.708-.708L8 7.293 4.854 4.146a.5.5 0 1 0-.708.708L7.293 8l-3.147 3.146a.5.5 0 0 0 .708.708L8 8.707l3.146 3.147a.5.5 0 0 0 .708-.708L8.707 8l3.147-3.146z"/>
                                            </svg>
                                            Checked In
                                        </span>
                                    </div>
                                @else
                                    <div class="px-6 py-3">
                                        <span class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs font-medium bg-teal-100 text-teal-800 rounded-full dark:bg-teal-500/10 dark:text-teal-500">
                                            <svg class="size-2.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 1 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                            </svg>
                                            Active
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="size-px whitespace-nowrap">
                                <div class="px-6 py-3">
                                    <span class="text-sm text-gray-500 dark:text-neutral-500">{{ $event->id }}-{{ $attendee->ticket_id }}-{{ $attendee->id }}</span>
                                </div>
                            </td>
                            <td class="size-px whitespace-nowrap">
                                <div class="px-6 py-3">
                                    <span class="text-sm text-gray-500 dark:text-neutral-500">{{ $event->currency ?? 'KES' }} {{ number_format($attendee->total_price ?? 0, 2) }}</span>
                                </div>
                            </td>
                            <td class="size-px whitespace-nowrap">
                                <div class="px-6 py-3 text-end">
                                    <a href="{{ route('ticket.pdf', $attendee->id) }}" 
                                       class="py-2 px-3 inline-flex items-center gap-x-2 rounded-md border border-gray-200 font-semibold bg-white text-gray-700 shadow-sm hover:bg-gray-50 text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700">
                                        Generate PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endif
                @if(!$hasAttendees)
                    <tr>
                        <td colspan="7" class="text-center py-4 text-gray-500 dark:text-neutral-400">No attendees registered for this event.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Attendee Modals -->
@include('partials.modal.delete-modal')
@include('partials.modal.alert-modal')