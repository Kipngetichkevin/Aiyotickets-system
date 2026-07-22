{{-- resources/views/partials/modal/event-create-modal.blade.php --}}
@props(['eventTypes' => [], 'merchants' => []])

<div x-data="eventCreateModal()" 
     x-init="init()"
     x-show="isOpen" 
     x-cloak
     class="fixed inset-0 z-[999999] overflow-y-auto"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     style="display: none;">
    
    <!-- Backdrop with 50% black opacity -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="close()"></div>
    
    <!-- Slideover Panel - Right Side -->
    <div class="fixed inset-y-0 right-0 flex max-w-full">
        <div class="relative w-screen max-w-2xl transform transition-transform duration-300 ease-in-out"
             :class="isOpen ? 'translate-x-0' : 'translate-x-full'">
            
            <div class="flex h-full flex-col overflow-y-auto bg-white shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="modalTitle">
                        Create New Event
                    </h2>
                    <button @click="close()" 
                            class="rounded-full bg-gray-100 p-2 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form Body -->
                <div class="flex-1 overflow-y-auto px-6 py-6">
                    <form id="event-form" 
                          method="POST" 
                          :action="formAction" 
                          enctype="multipart/form-data"
                          @submit.prevent="submitForm">
                        @csrf
                        <input type="hidden" name="_method" x-model="formMethod">
                        
                        <!-- Hidden Fields -->
                        <input type="hidden" name="merchant_id" value="{{ auth()->user()->merchant->id ?? '' }}">
                        <input type="hidden" name="user_id" value="{{ auth()->id() }}">

                        <div class="space-y-6">
                            <!-- Event Name -->
                            <div>
                                <label for="modal-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Event Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="modal-name" required
                                    x-model="formData.name"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="Enter event name">
                            </div>

                            <!-- Event Type & Currency -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="modal-event_type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Event Type <span class="text-red-500">*</span>
                                    </label>
                                    <select name="event_type_id" id="modal-event_type_id" 
                                        x-model="formData.event_type_id"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        <option value="">Select Event Type</option>
                                        @forelse($eventTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @empty
                                            <option value="" disabled>No event types available</option>
                                        @endforelse
                                    </select>
                                </div>
                                <div>
                                    <label for="modal-currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Currency <span class="text-red-500">*</span>
                                    </label>
                                    <select name="currency" id="modal-currency" required
                                        x-model="formData.currency"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        <option value="KES">KES</option>
                                        <option value="USD">USD</option>
                                        <option value="USDT">USDT</option>
                                        <option value="EUROS">EUROS</option>
                                        <option value="POUND">POUND</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Description with Quill -->
                            <div>
                                <label for="modal-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Description
                                </label>
                                <div id="quill-editor" class="mt-1" style="height: 200px;"></div>
                                <textarea name="description" id="modal-description" style="display:none;"></textarea>
                            </div>

                            <!-- Date & End Date with Flatpickr -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="modal-date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Start Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="date" 
                                           id="modal-date" 
                                           required
                                           class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer"
                                           placeholder="Select start date and time">
                                </div>
                                <div>
                                    <label for="modal-end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        End Date
                                    </label>
                                    <input type="text" 
                                           name="end_date" 
                                           id="modal-end_date"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer"
                                           placeholder="Select end date and time">
                                </div>
                            </div>

                            <!-- Location -->
                            <div>
                                <label for="modal-location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Location <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="location" id="modal-location" required
                                    x-model="formData.location"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="Event location">
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="modal-status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select name="status" id="modal-status" required
                                    x-model="formData.status"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>

                            <!-- Image Upload with Preview -->
                            <div>
                                <label for="modal-image" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Event Image
                                </label>
                                <div class="mt-1 flex justify-center rounded-lg border-2 border-dashed border-gray-300 px-6 py-10 dark:border-gray-700">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="mt-4 flex text-sm leading-6 text-gray-600 dark:text-gray-400">
                                            <label for="modal-image-upload" class="relative cursor-pointer rounded-md bg-white font-semibold text-blue-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-600 focus-within:ring-offset-2 hover:text-blue-500 dark:bg-gray-800">
                                                <span>Upload a file</span>
                                                <input id="modal-image-upload" name="image" type="file" accept="image/*" 
                                                    class="sr-only"
                                                    @change="handleImageUpload($event)">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs leading-5 text-gray-600 dark:text-gray-400">PNG, JPG, WebP up to 2MB</p>
                                    </div>
                                </div>
                                
                                <!-- Image Preview -->
                                <div class="mt-3" x-show="formData.image_preview || formData.current_image">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image Preview:</p>
                                    <div class="relative inline-block">
                                        <img :src="formData.image_preview || formData.current_image" 
                                             class="h-32 w-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700" 
                                             alt="Event image">
                                        <button type="button" 
                                                @click="removeImage()"
                                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors"
                                                title="Remove image">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1" x-show="formData.new_image" x-text="formData.new_image_name"></p>
                                    <p class="text-xs text-gray-500 mt-1" x-show="!formData.new_image && formData.current_image">Current image</p>
                                </div>
                            </div>

                            <!-- Banner Upload with Preview -->
                            <div>
                                <label for="modal-banner" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Event Banner
                                </label>
                                <div class="mt-1 flex justify-center rounded-lg border-2 border-dashed border-gray-300 px-6 py-10 dark:border-gray-700">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="mt-4 flex text-sm leading-6 text-gray-600 dark:text-gray-400">
                                            <label for="modal-banner-upload" class="relative cursor-pointer rounded-md bg-white font-semibold text-blue-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-600 focus-within:ring-offset-2 hover:text-blue-500 dark:bg-gray-800">
                                                <span>Upload a banner</span>
                                                <input id="modal-banner-upload" name="banner" type="file" accept="image/*" 
                                                    class="sr-only"
                                                    @change="handleBannerUpload($event)">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs leading-5 text-gray-600 dark:text-gray-400">PNG, JPG, WebP up to 2MB</p>
                                    </div>
                                </div>
                                
                                <!-- Banner Preview -->
                                <div class="mt-3" x-show="formData.banner_preview || formData.current_banner">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Banner Preview:</p>
                                    <div class="relative inline-block">
                                        <img :src="formData.banner_preview || formData.current_banner" 
                                             class="h-32 w-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700" 
                                             alt="Event banner">
                                        <button type="button" 
                                                @click="removeBanner()"
                                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors"
                                                title="Remove banner">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1" x-show="formData.new_banner" x-text="formData.new_banner_name"></p>
                                    <p class="text-xs text-gray-500 mt-1" x-show="!formData.new_banner && formData.current_banner">Current banner</p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer Actions -->
                <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                    <div class="flex justify-end gap-3">
                        <button @click="close()" 
                                type="button"
                                class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            Cancel
                        </button>
                        <button type="submit" 
                                form="event-form"
                                x-bind:disabled="isSubmitting"
                                :class="{'opacity-50 cursor-not-allowed': isSubmitting}"
                                class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <span x-text="isSubmitting ? 'Saving...' : submitButtonText"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load Quill CSS and JS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<!-- Load Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('eventCreateModal', function() {
        return {
            isOpen: false,
            modalTitle: 'Create New Event',
            submitButtonText: 'Create Event',
            formAction: '{{ route("events.store") }}',
            formMethod: 'POST',
            isEditing: false,
            isSubmitting: false,
            eventId: null,
            datePickerInstance: null,
            endDatePickerInstance: null,
            quillInstance: null,
            quillInitialized: false,
            formData: {
                name: '',
                event_type_id: '',
                currency: 'KES',
                description: '',
                date: '',
                end_date: '',
                location: '',
                status: 'active',
                current_image: null,
                current_banner: null,
                image_preview: null,
                banner_preview: null,
                new_image: null,
                new_banner: null,
                new_image_name: '',
                new_banner_name: ''
            },
            
            init() {
                // Listen for open create modal event
                window.addEventListener('open-event-create-modal', () => {
                    this.resetForm();
                    this.isEditing = false;
                    this.modalTitle = 'Create New Event';
                    this.submitButtonText = 'Create Event';
                    this.formAction = '{{ route("events.store") }}';
                    this.formMethod = 'POST';
                    this.open();
                });
                
                // Listen for open edit modal event
                window.addEventListener('open-event-edit-modal', (event) => {
                    const eventData = event.detail;
                    console.log('Edit event data received:', eventData);
                    this.loadEventData(eventData);
                    this.isEditing = true;
                    this.eventId = eventData.id;
                    this.modalTitle = 'Edit Event';
                    this.submitButtonText = 'Update Event';
                    this.formAction = `/events/${eventData.id}`;
                    this.formMethod = 'PUT';
                    this.open();
                });
            },
            
            loadEventData(event) {
                console.log('Loading event data:', event);
                
                // Format date for Flatpickr
                const formatDateForFlatpickr = (dateStr) => {
                    if (!dateStr) return '';
                    try {
                        const date = new Date(dateStr);
                        if (isNaN(date.getTime())) return '';
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        const hours = String(date.getHours()).padStart(2, '0');
                        const minutes = String(date.getMinutes()).padStart(2, '0');
                        return `${year}-${month}-${day}T${hours}:${minutes}`;
                    } catch (e) {
                        console.error('Date formatting error:', e);
                        return '';
                    }
                };
                
                // Get the full URL for images
                const getImageUrl = (path) => {
                    if (!path) return null;
                    
                    // If it's already a full URL
                    if (path.startsWith('http://') || path.startsWith('https://')) {
                        return path;
                    }
                    
                    // If it's a storage path
                    if (path.startsWith('/storage/')) {
                        return path;
                    }
                    
                    // If it starts with 'storage/'
                    if (path.startsWith('storage/')) {
                        return '/' + path;
                    }
                    
                    // Default: assume it's a storage path
                    return '/storage/' + path;
                };
                
                // Get image URL from various possible fields
                const imageUrl = event.image_url || event.image || null;
                const bannerUrl = event.banner_url || event.banner || null;
                
                this.formData = {
                    name: event.name || '',
                    event_type_id: event.event_type_id || '',
                    currency: event.currency || 'KES',
                    description: event.description || '',
                    date: formatDateForFlatpickr(event.date),
                    end_date: formatDateForFlatpickr(event.end_date),
                    location: event.location || '',
                    status: event.status || 'active',
                    current_image: getImageUrl(imageUrl),
                    current_banner: getImageUrl(bannerUrl),
                    image_preview: null,
                    banner_preview: null,
                    new_image: null,
                    new_banner: null,
                    new_image_name: '',
                    new_banner_name: ''
                };
                
                console.log('Form data after loading:', this.formData);
                console.log('Current image URL:', this.formData.current_image);
                console.log('Current banner URL:', this.formData.current_banner);
                
                // Set description in Quill after it's initialized
                this.$nextTick(() => {
                    this.updateQuillContent(this.formData.description);
                    
                    // Update Flatpickr instances with new values
                    if (this.datePickerInstance && this.formData.date) {
                        try {
                            this.datePickerInstance.setDate(this.formData.date, false);
                        } catch (e) {
                            console.error('Error setting date:', e);
                        }
                    }
                    if (this.endDatePickerInstance && this.formData.end_date) {
                        try {
                            this.endDatePickerInstance.setDate(this.formData.end_date, false);
                        } catch (e) {
                            console.error('Error setting end date:', e);
                        }
                    }
                });
            },
            
            updateQuillContent(content) {
                if (this.quillInstance && this.quillInitialized) {
                    try {
                        this.quillInstance.root.innerHTML = content || '';
                        console.log('Quill content updated');
                    } catch (e) {
                        console.error('Error updating Quill content:', e);
                    }
                } else {
                    console.log('Quill not initialized yet, content will be set later');
                    // Store the content to set when Quill is initialized
                    this._pendingQuillContent = content;
                }
            },
            
            resetForm() {
                this.formData = {
                    name: '',
                    event_type_id: '',
                    currency: 'KES',
                    description: '',
                    date: '',
                    end_date: '',
                    location: '',
                    status: 'active',
                    current_image: null,
                    current_banner: null,
                    image_preview: null,
                    banner_preview: null,
                    new_image: null,
                    new_banner: null,
                    new_image_name: '',
                    new_banner_name: ''
                };
                this.eventId = null;
                
                // Reset Quill content
                if (this.quillInstance && this.quillInitialized) {
                    this.quillInstance.root.innerHTML = '';
                }
                
                // Clear Flatpickr instances
                if (this.datePickerInstance) {
                    this.datePickerInstance.clear();
                }
                if (this.endDatePickerInstance) {
                    this.endDatePickerInstance.clear();
                }
            },
            
            open() {
                this.isOpen = true;
                document.body.style.overflow = 'hidden';
                
                // Initialize Quill and Flatpickr after the modal is open
                this.$nextTick(() => {
                    this.initQuill();
                    this.initFlatpickr();
                });
            },
            
            close() {
                this.isOpen = false;
                document.body.style.overflow = '';
                
                // Destroy Flatpickr instances
                if (this.datePickerInstance) {
                    this.datePickerInstance.destroy();
                    this.datePickerInstance = null;
                }
                if (this.endDatePickerInstance) {
                    this.endDatePickerInstance.destroy();
                    this.endDatePickerInstance = null;
                }
                
                // Reset Quill but keep instance
                if (this.quillInstance && this.quillInitialized) {
                    this.quillInstance.root.innerHTML = '';
                }
                
                this.resetForm();
            },
            
            initQuill() {
                if (typeof Quill === 'undefined') {
                    console.warn('Quill is not loaded');
                    return;
                }
                
                // Prevent double initialization
                if (this.quillInitialized && this.quillInstance) {
                    console.log('Quill already initialized');
                    return;
                }
                
                const editorContainer = document.getElementById('quill-editor');
                if (!editorContainer) {
                    console.warn('Quill editor container not found');
                    return;
                }
                
                // Check if Quill is already initialized on this element
                if (editorContainer.querySelector('.ql-editor')) {
                    console.log('Quill already exists on this element');
                    return;
                }
                
                try {
                    // Initialize Quill
                    this.quillInstance = new Quill(editorContainer, {
                        theme: 'snow',
                        placeholder: 'Write event description...',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                ['blockquote', 'code-block'],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                ['link', 'image'],
                                ['clean']
                            ]
                        }
                    });
                    
                    this.quillInitialized = true;
                    console.log('Quill initialized successfully');
                    
                    // Set initial content if editing
                    if (this.formData.description) {
                        this.quillInstance.root.innerHTML = this.formData.description;
                    } else if (this._pendingQuillContent) {
                        this.quillInstance.root.innerHTML = this._pendingQuillContent;
                        this._pendingQuillContent = null;
                    }
                    
                    // Update textarea on change
                    this.quillInstance.on('text-change', () => {
                        const description = this.quillInstance.root.innerHTML;
                        this.formData.description = description;
                        document.getElementById('modal-description').value = description;
                    });
                } catch (error) {
                    console.error('Error initializing Quill:', error);
                }
            },
            
            initFlatpickr() {
                // Check if Flatpickr is available
                if (typeof flatpickr === 'undefined') {
                    console.warn('Flatpickr is not loaded');
                    return;
                }
                
                // Destroy existing instances if any
                if (this.datePickerInstance) {
                    this.datePickerInstance.destroy();
                    this.datePickerInstance = null;
                }
                if (this.endDatePickerInstance) {
                    this.endDatePickerInstance.destroy();
                    this.endDatePickerInstance = null;
                }
                
                // Get the date picker elements
                const datePickerEl = document.getElementById('modal-date');
                const endDatePickerEl = document.getElementById('modal-end_date');
                
                if (!datePickerEl) {
                    console.warn('Date picker element not found');
                    return;
                }
                
                // Initialize Date Picker
                this.datePickerInstance = flatpickr(datePickerEl, {
                    enableTime: true,
                    dateFormat: "Y-m-d\\TH:i",
                    time_24hr: true,
                    minDate: "today",
                    allowInput: false,
                    onChange: (selectedDates, dateStr) => {
                        this.formData.date = dateStr;
                    }
                });
                
                // Initialize End Date Picker
                if (endDatePickerEl) {
                    this.endDatePickerInstance = flatpickr(endDatePickerEl, {
                        enableTime: true,
                        dateFormat: "Y-m-d\\TH:i",
                        time_24hr: true,
                        minDate: "today",
                        allowInput: false,
                        onChange: (selectedDates, dateStr) => {
                            this.formData.end_date = dateStr;
                        }
                    });
                }
                
                // If editing, set the dates
                if (this.formData.date) {
                    try {
                        this.datePickerInstance.setDate(this.formData.date, false);
                    } catch (e) {
                        console.error('Error setting date:', e);
                    }
                }
                if (this.endDatePickerInstance && this.formData.end_date) {
                    try {
                        this.endDatePickerInstance.setDate(this.formData.end_date, false);
                    } catch (e) {
                        console.error('Error setting end date:', e);
                    }
                }
            },
            
            handleImageUpload(event) {
                const file = event.target.files[0];
                if (file) {
                    // Validate file type
                    if (!file.type.startsWith('image/')) {
                        this.showAlert('error', 'Invalid File', 'Please upload a valid image file.');
                        return;
                    }
                    
                    // Validate file size (2MB max)
                    if (file.size > 2 * 1024 * 1024) {
                        this.showAlert('error', 'File Too Large', 'Image must be less than 2MB.');
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.formData.image_preview = e.target.result;
                        this.formData.new_image = file;
                        this.formData.new_image_name = file.name;
                        // Clear current image when new one is uploaded
                        this.formData.current_image = null;
                    };
                    reader.readAsDataURL(file);
                }
            },
            
            handleBannerUpload(event) {
                const file = event.target.files[0];
                if (file) {
                    // Validate file type
                    if (!file.type.startsWith('image/')) {
                        this.showAlert('error', 'Invalid File', 'Please upload a valid image file.');
                        return;
                    }
                    
                    // Validate file size (2MB max)
                    if (file.size > 2 * 1024 * 1024) {
                        this.showAlert('error', 'File Too Large', 'Banner must be less than 2MB.');
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.formData.banner_preview = e.target.result;
                        this.formData.new_banner = file;
                        this.formData.new_banner_name = file.name;
                        // Clear current banner when new one is uploaded
                        this.formData.current_banner = null;
                    };
                    reader.readAsDataURL(file);
                }
            },
            
            removeImage() {
                this.formData.image_preview = null;
                this.formData.new_image = null;
                this.formData.new_image_name = '';
                // Reset file input
                const fileInput = document.getElementById('modal-image-upload');
                if (fileInput) fileInput.value = '';
            },
            
            removeBanner() {
                this.formData.banner_preview = null;
                this.formData.new_banner = null;
                this.formData.new_banner_name = '';
                // Reset file input
                const fileInput = document.getElementById('modal-banner-upload');
                if (fileInput) fileInput.value = '';
            },
            
            showAlert(type, title, message) {
                window.dispatchEvent(new CustomEvent('show-alert', {
                    detail: { type, title, message }
                }));
            },
            
            submitForm() {
                this.isSubmitting = true;
                
                // Get form element
                const form = document.getElementById('event-form');
                const formData = new FormData(form);
                
                // Add the description from Quill
                if (this.quillInstance && this.quillInitialized) {
                    const description = this.quillInstance.root.innerHTML;
                    formData.set('description', description);
                }
                
                // If editing, append the event ID
                if (this.isEditing && this.eventId) {
                    formData.append('_method', 'PUT');
                }
                
                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                
                console.log('Submitting form to:', form.action);
                console.log('Form data:', Object.fromEntries(formData));
                
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(async response => {
                    let data;
                    try {
                        data = await response.json();
                    } catch (e) {
                        throw new Error('Invalid JSON response from server');
                    }
                    
                    if (!response.ok) {
                        throw new Error(data.message || data.error || `HTTP error ${response.status}`);
                    }
                    return data;
                })
                .then(data => {
                    this.isSubmitting = false;
                    console.log('Response data:', data);
                    
                    if (data.success) {
                        this.showAlert('success', 'Success', data.message);
                        this.close();
                        
                        // Reload the page or redirect
                        if (data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1500);
                        } else {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        }
                    } else {
                        this.showAlert('error', 'Error', data.message || 'Something went wrong.');
                    }
                })
                .catch(error => {
                    this.isSubmitting = false;
                    console.error('Error:', error);
                    
                    // Handle CSRF token errors
                    if (error.message.includes('403') || error.message.includes('CSRF')) {
                        this.showAlert('error', 'Session Expired', 'Your session has expired. Please refresh the page and try again.');
                    } else if (error.message.includes('422')) {
                        this.showAlert('error', 'Validation Error', 'Please check your form inputs and try again.');
                    } else {
                        this.showAlert('error', 'Error', error.message || 'An unexpected error occurred. Please try again.');
                    }
                });
            }
        };
    });
});
</script>