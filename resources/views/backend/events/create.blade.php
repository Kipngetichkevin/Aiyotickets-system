@vite('resources/css/app.css')

@include('backend.includes.header')
@include('backend.includes.Sidebar')

<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
    <!-- Content -->
    <div class="w-full lg:ps-64">
        <div class="max-w-4xl px-4 py-10 sm:px-6 lg:px-8 mx-auto">
            <!-- Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 dark:bg-neutral-800">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-neutral-200">Create Event</h2>
                    @include('includes.Messages')
                </div>

                <form method="POST" action="{{ route('backend.events.store') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Hidden Fields -->
                    <input type="hidden" name="merchant_id" value="{{ auth()->id() }}">
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">

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
                                    <option value="{{ $eventType->id }}" class="dark:bg-neutral-800">{{ $eventType->name }}</option>
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
                                <option value="KES">KES</option>
                                <option value="USD">USD</option>
                                <option value="USDT">USDT</option>
                                <option value="EUROS">EUROS</option>
                                <option value="POUND">POUND</option>
                            </select>
                        </div>

                        <!-- Name -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Event Name</label>
                            <input type="text" id="name" name="name" required
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200"
                                placeholder="Event Name">
                        </div>

                        <!-- Description -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Description</label>
                            <div class="dark:bg-neutral-900 rounded-lg overflow-hidden">
                                <textarea id="description" name="description" required
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:border-neutral-700 dark:text-neutral-200"
                                    rows="6"></textarea>
                            </div>
                        </div>

                        <!-- Date & End Date -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Start Date</label>
                            <input type="datetime-local" id="date" name="date" required
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">End Date</label>
                            <input type="datetime-local" id="end_date" name="end_date"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200">
                        </div>

                        <!-- Location -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Location</label>
                            <input type="text" id="location" name="location" required
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200"
                                placeholder="Event Location">
                        </div>

                        <!-- Status -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Status</label>
                            <select id="status" name="status" required
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200
                                       bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGhlaWdodD0iMTYiIHdpZHRoPSIxNiIgY2xhc3M9ImJpIGJpLWNoZXZyb24tZG93biIgZmlsbD0iY3VycmVudENvbG9yIj48cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xLjY0NiA0LjY0NmEuNS41IDAgMCAxIC43MDggMEw4IDEwLjI5M2w1LjY0Ni01LjY0N2EuNS41IDAgMCAxIC43MDguNzA4bC02IDZhLjUuNSAwIDAgMS0uNzA4IDBsLTYtNmEuNS41IDAgMCAxIDAtLjcwOHoiLz48L3N2Zz4=')] 
                                       bg-no-repeat bg-[center_right_1rem]">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <!-- File Uploads -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Event Image</label>
                            <input type="file" id="image" name="image" accept="image/*"
                                   style="width: 100%; padding: 12px; border: 2px dashed #cbd5e1; border-radius: 10px; background: #f8fafc; cursor: pointer; font-size: 14px; color: #374151;">
                            <p style="font-size: 12px; color: #6b7280; margin: 6px 0 0 0;">
                                Upload a square image (JPG/PNG, max 5 MB).
                            </p>
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">Event Banner</label>
                            <input type="file" id="banner" name="banner" accept="image/*"
                                   style="width: 100%; padding: 12px; border: 2px dashed #cbd5e1; border-radius: 10px; background: #f8fafc; cursor: pointer; font-size: 14px; color: #374151;">
                            <p style="font-size: 12px; color: #6b7280; margin: 6px 0 0 0;">
                                Upload a wide image (JPG/PNG, max 5 MB).
                            </p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-8 flex justify-end gap-x-3">
                        <a href="{{ url()->previous() }}"
                            class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-200">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
                            Create Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Required plugins -->
<script src="https://cdn.jsdelivr.net/npm/preline/dist/preline.min.js"></script>
<script src="https://cdn.tiny.cloud/1/hdwhfaeo6baam0bcwi3t0g4if6viqjugkyvl1p5y583c5zkr/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
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
</script>