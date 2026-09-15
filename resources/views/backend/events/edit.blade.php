@vite('resources/css/app.css')

@include('backend.includes.header')
@include('backend.includes.Sidebar')
<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">

    <!-- Content -->
    <div class="w-full lg:ps-64">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-neutral-200 mb-6">Edit Event</h2>
        @include('includes.Messages')
        <!-- Card Section -->
        <div class="max-w-4xl px-4 py-10 sm:px-6 lg:px-8 mx-auto">
            <!-- Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 dark:bg-neutral-800">
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-neutral-200">
                        Event Details
                    </h2>
                </div>

                <form method="POST" action="{{ route('backend.events.update', ['event' => $event->id]) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Hidden Fields -->
                    <input type="hidden" id="merchant_id" name="merchant_id" value="{{ $event->merchant_id }}">
                    <input type="hidden" id="user_id" name="user_id" value="{{ $event->user_id }}">

                    <!-- Form Grid -->
                    <div class="grid gap-4 md:grid-cols-2">
                        <!-- Event Type -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Event Type</label>
                            <select id="event_type_id" name="event_type_id" required
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200
                                       bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGhlaWdodD0iMTYiIHdpZHRoPSIxNiIgY2xhc3M9ImJpIGJpLWNoZXZyb24tZG93biIgZmlsbD0iY3VycmVudENvbG9yIj48cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xLjY0NiA0LjY0NmEuNS41IDAgMCAxIC43MDggMEw4IDEwLjI5M2w1LjY0Ni01LjY0N2EuNS41IDAgMCAxIC43MDguNzA4bC02IDZhLjUuNSAwIDAgMS0uNzA4IDBsLTYtNmEuNS41IDAgMCAxIDAtLjcwOHoiLz48L3N2Zz4=')] 
                                       bg-no-repeat bg-[center_right_1rem]">
                                <option value="">Select Event Type</option>
                                @foreach ($eventTypes as $eventType)
                                    <option value="{{ $eventType->id }}" {{ $eventType->id == $event->event_type_id ? 'selected' : '' }} class="dark:bg-neutral-800">
                                        {{ $eventType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Currency -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Currency</label>
                            <select id="currency" name="currency" required
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200
                                       bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGhlaWdodD0iMTYiIHdpZHRoPSIxNiIgY2xhc3M9ImJpIGJpLWNoZXZyb24tZG93biIgZmlsbD0iY3VycmVudENvbG9yIj48cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xLjY0NiA0LjY0NmEuNS41IDAgMCAxIC43MDggMEw4IDEwLjI5M2w1LjY0Ni01LjY0N2EuNS41IDAgMCAxIC43MDguNzA4bC02IDZhLjUuNSAwIDAgMS0uNzA4IDBsLTYtNmEuNS41IDAgMCAxIDAtLjcwOHoiLz48L3N2Zz4=')] 
                                       bg-no-repeat bg-[center_right_1rem]">
                                <option value="KES" {{ $event->currency == 'KES' ? 'selected' : '' }}>KES</option>
                                <option value="USD" {{ $event->currency == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="USDT" {{ $event->currency == 'USDT' ? 'selected' : '' }}>USDT</option>
                                <option value="EUROS" {{ $event->currency == 'EUROS' ? 'selected' : '' }}>EUROS</option>
                                <option value="POUND" {{ $event->currency == 'POUND' ? 'selected' : '' }}>POUND</option>
                            </select>
                        </div>

                        <!-- Name -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Event Name</label>
                            <input type="text" id="name" name="name" required value="{{ $event->name }}"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200"
                                placeholder="Event Name">
                        </div>

                        <!-- Status -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Status</label>
                            <select id="status" name="status" required
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200
                                       bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGhlaWdodD0iMTYiIHdpZHRoPSIxNiIgY2xhc3M9ImJpIGJpLWNoZXZyb24tZG93biIgZmlsbD0iY3VycmVudENvbG9yIj48cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xLjY0NiA0LjY0NmEuNS41IDAgMCAxIC43MDggMEw4IDEwLjI5M2w1LjY0Ni01LjY0N2EuNS41IDAgMCAxIC43MDguNzA4bC02IDZhLjUuNSAwIDAgMS0uNzA4IDBsLTYtNmEuNS41IDAgMCAxIDAtLjcwOHoiLz48L3N2Zz4=')] 
                                       bg-no-repeat bg-[center_right_1rem]">
                                <option value="active" {{ $event->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $event->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="cancelled" {{ $event->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="completed" {{ $event->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>

                        <!-- Date -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Start Date</label>
                            <input type="datetime-local" id="date" name="date" required value="{{ $event->date }}"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                        </div>

                        <!-- End Date -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">End Date</label>
                            <input type="datetime-local" id="end_date" name="end_date" value="{{ $event->end_date }}"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                        </div>

                        <!-- Location -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Location</label>
                            <input type="text" id="location" name="location" required value="{{ $event->location }}"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200"
                                placeholder="Event Location">
                        </div>

                        <!-- Description -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Description</label>
                            <textarea id="description" name="description" required rows="4"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200"
                                placeholder="Event Description">{{ $event->description }}</textarea>
                        </div>

                        <!-- Image Upload -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Event Image</label>
                            <div class="flex items-center gap-4">
                                <div class="relative w-full">
                                    <input type="file" id="image" name="image"
                                        class="opacity-0 absolute w-full h-full cursor-pointer">
                                    <div class="px-4 py-8 w-full border-2 border-dashed rounded-lg text-center
                                         dark:border-neutral-700 hover:border-blue-500 transition-colors">
                                        <span class="text-gray-500 dark:text-neutral-400">Click to upload image</span>
                                    </div>
                                </div>
                                @if ($event->image)
                                    <img src="{{ $event->image }}" alt="Current Image" class="w-24 h-24 rounded-lg object-cover">
                                @endif
                            </div>
                        </div>

                        <!-- Banner Upload -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Event Banner</label>
                            <div class="flex items-center gap-4">
                                <div class="relative w-full">
                                    <input type="file" id="banner" name="banner"
                                        class="opacity-0 absolute w-full h-full cursor-pointer">
                                    <div class="px-4 py-8 w-full border-2 border-dashed rounded-lg text-center
                                         dark:border-neutral-700 hover:border-blue-500 transition-colors">
                                        <span class="text-gray-500 dark:text-neutral-400">Click to upload banner</span>
                                    </div>
                                </div>
                                @if ($event->banner)
                                    <img src="{{ $event->banner }}" alt="Current Banner" class="w-24 h-24 rounded-lg object-cover">
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-8 flex justify-end gap-x-3">
                        <button type="button"
                            class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-200">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
                            Update Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/preline/dist/preline.min.js"></script>
</div>