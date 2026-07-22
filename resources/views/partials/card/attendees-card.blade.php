{{-- partials/card/attendees-card.blade.php --}}
@props(['attendees' => []])

<div class="flex flex-col">
    <div class="-m-1.5 overflow-x-auto">
        <div class="p-1.5 min-w-full inline-block align-middle">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-800 dark:border-neutral-700">
                <!-- Header -->
                <div class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:border-neutral-700">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Recent Attendees</h2>
                        <p class="text-sm text-gray-600 dark:text-neutral-400">Latest ticket purchases and attendees.</p>
                    </div>
                    <div>
                        <div class="inline-flex gap-x-2">
                            <a class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 dark:bg-transparent dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800" 
                               href="{{ route('events.index') }}">
                                View all
                            </a>
                        </div>
                    </div>
                </div>
                <!-- End Header -->

                <!-- Table -->
                <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                    <thead class="bg-gray-50 dark:bg-neutral-800">
                        <tr>
                            <th scope="col" class="ps-6 py-3 text-start">
                                <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Name</span>
                            </th>
                            <th scope="col" class="px-6 py-3 text-start">
                                <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Email</span>
                            </th>
                            <th scope="col" class="px-6 py-3 text-start">
                                <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Phone</span>
                            </th>
                            <th scope="col" class="px-6 py-3 text-start">
                                <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Tickets</span>
                            </th>
                            <th scope="col" class="px-6 py-3 text-start">
                                <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Payment Status</span>
                            </th>
                            <th scope="col" class="px-6 py-3 text-start">
                                <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Created</span>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                        @forelse($attendees as $attendee)
                            <tr>
                                <td class="size-px whitespace-nowrap">
                                    <div class="ps-6 lg:ps-3 xl:ps-0 pe-6 py-3">
                                        <div class="flex items-center gap-x-3">
                                            <div class="grow">
                                                <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ $attendee->name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="h-px w-72 whitespace-nowrap">
                                    <div class="px-6 py-3">
                                        <span class="block text-sm text-gray-500 dark:text-neutral-500">{{ $attendee->email ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="size-px whitespace-nowrap">
                                    <div class="px-6 py-3">
                                        <span class="text-sm text-gray-500 dark:text-neutral-500">{{ $attendee->phone ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="size-px whitespace-nowrap">
                                    <div class="px-6 py-3">
                                        <span class="text-sm text-gray-500 dark:text-neutral-500">{{ $attendee->qty ?? 0 }}</span>
                                    </div>
                                </td>
                                <td class="size-px whitespace-nowrap">
                                    <div class="px-6 py-3">
                                        <span class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs font-medium {{ ($attendee->payment_status ?? 'pending') == 'paid' ? 'bg-teal-100 text-teal-800 dark:bg-teal-500/10 dark:text-teal-500' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-500' }} rounded-full">
                                            {{ ucfirst($attendee->payment_status ?? 'Pending') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="size-px whitespace-nowrap">
                                    <div class="px-6 py-3">
                                        <span class="text-sm text-gray-500 dark:text-neutral-500">{{ $attendee->created_at ? $attendee->created_at->format('M d, Y') : 'N/A' }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No attendees found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <!-- End Table -->

                <!-- Footer -->
                <div class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-t border-gray-200 dark:border-neutral-700">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-neutral-400">
                            <span class="font-semibold text-gray-800 dark:text-neutral-200">{{ $attendees->count() }}</span> recent attendees
                        </p>
                    </div>
                </div>
                <!-- End Footer -->
            </div>
        </div>
    </div>
</div>