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
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="close()"></div>
    
    <!-- Slideover Panel - Right Side -->
    <div class="fixed inset-y-0 right-0 flex max-w-full">
        <div class="relative w-screen max-w-2xl transform transition-transform duration-300 ease-in-out"
             :class="isOpen ? 'translate-x-0' : 'translate-x-full'">
            
            <!-- ⚠️ FORM wraps the entire panel — submit button is INSIDE the form -->
            <form id="event-form" 
                  method="POST" 
                  enctype="multipart/form-data"
                  @submit.prevent="submitForm"
                  class="flex h-full flex-col overflow-hidden bg-white shadow-xl dark:bg-gray-900">
                @csrf
                <input type="hidden" name="_method" :value="isEditing ? 'PUT' : 'POST'">
                <input type="hidden" name="user_id" value="{{ auth()->id() }}">

                <!-- Header -->
                <div class="flex shrink-0 items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="modalTitle">
                        Create New Event
                    </h2>
                    <button type="button" @click="close()" 
                            class="rounded-full bg-gray-100 p-2 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Body (scrolls) -->
                <div class="flex-1 overflow-y-auto px-6 py-6">

                    <!-- Error box -->
                    <div x-show="errorMessage" x-cloak
                         class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
                        <p class="font-semibold mb-1">Could not save</p>
                        <div x-html="errorMessage" class="whitespace-pre-wrap"></div>
                    </div>

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
                                <select name="event_type_id" id="modal-event_type_id" required
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

                        <!-- Date & End Date — native HTML5 datetime-local -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="modal-date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Start Date &amp; Time <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local" 
                                       name="date" 
                                       id="modal-date" 
                                       required
                                       x-model="formData.date"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                            <div>
                                <label for="modal-end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    End Date &amp; Time
                                </label>
                                <input type="datetime-local" 
                                       name="end_date" 
                                       id="modal-end_date"
                                       x-model="formData.end_date"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
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
                </div>

                <!-- Footer (fixed) -->
                <div class="shrink-0 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="close()" 
                                class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            Cancel
                        </button>
                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-text="isSubmitting ? 'Saving...' : submitButtonText"></span>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- Load Quill CSS and JS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('eventCreateModal', function() {
        return {
            isOpen: false,
            modalTitle: 'Create New Event',
            submitButtonText: 'Create Event',
            isEditing: false,
            isSubmitting: false,
            eventId: null,
            errorMessage: '',
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
                merchant_id: '',
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
                // Open CREATE modal
                window.addEventListener('open-event-create-modal', () => {
                    this.resetForm();
                    this.isEditing = false;
                    this.modalTitle = 'Create New Event';
                    this.submitButtonText = 'Create Event';
                    this.open();
                });
                
                // Open EDIT modal
                window.addEventListener('open-event-edit-modal', (event) => {
                    const eventData = event.detail;
                    this.loadEventData(eventData);
                    this.isEditing = true;
                    this.eventId = eventData.id;
                    this.modalTitle = 'Edit Event';
                    this.submitButtonText = 'Update Event';
                    this.open();
                });
            },
            
            loadEventData(event) {
                const formatDateForInput = (dateStr) => {
                    if (!dateStr) return '';
                    try {
                        const date = new Date(dateStr);
                        if (isNaN(date.getTime())) return '';
                        const pad = (n) => String(n).padStart(2, '0');
                        return `${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
                    } catch (e) { return ''; }
                };
                
                const getImageUrl = (path) => {
                    if (!path) return null;
                    if (path.startsWith('http://') || path.startsWith('https://')) return path;
                    if (path.startsWith('/storage/')) return path;
                    if (path.startsWith('storage/')) return '/' + path;
                    return '/storage/' + path;
                };
                
                this.formData = {
                    name: event.name || '',
                    event_type_id: event.event_type_id || '',
                    currency: event.currency || 'KES',
                    description: event.description || '',
                    date: formatDateForInput(event.date),
                    end_date: formatDateForInput(event.end_date),
                    location: event.location || '',
                    status: event.status || 'active',
                    merchant_id: event.merchant_id || '',
                    current_image: getImageUrl(event.image_url || event.image || null),
                    current_banner: getImageUrl(event.banner_url || event.banner || null),
                    image_preview: null,
                    banner_preview: null,
                    new_image: null,
                    new_banner: null,
                    new_image_name: '',
                    new_banner_name: ''
                };
                
                
                this.$nextTick(() => {
                    this.updateQuillContent(this.formData.description);
                });
            },
            
            updateQuillContent(content) {
                if (this.quillInstance && this.quillInitialized) {
                    this.quillInstance.root.innerHTML = content || '';
                } else {
                    this._pendingQuillContent = content;
                }
            },
            
            resetForm() {
                this.formData = {
                    name: '', event_type_id: '', currency: 'KES', description: '',
                    date: '', end_date: '', location: '', status: 'active', merchant_id: '',
                    current_image: null, current_banner: null,
                    image_preview: null, banner_preview: null,
                    new_image: null, new_banner: null,
                    new_image_name: '', new_banner_name: ''
                };
                this.eventId = null;
                this.errorMessage = '';
                if (this.quillInstance && this.quillInitialized) {
                    this.quillInstance.root.innerHTML = '';
                }
            },
            
            open() {
                this.isOpen = true;
                document.body.style.overflow = 'hidden';
                this.$nextTick(() => this.initQuill());
            },
            
            close() {
                this.isOpen = false;
                document.body.style.overflow = '';
                if (this.quillInstance && this.quillInitialized) {
                    this.quillInstance.root.innerHTML = '';
                }
                this.resetForm();
            },
            
            initQuill() {
                if (typeof Quill === 'undefined') return;
                if (this.quillInitialized && this.quillInstance) return;
                
                const editorContainer = document.getElementById('quill-editor');
                if (!editorContainer) return;
                if (editorContainer.querySelector('.ql-editor')) return;
                
                try {
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
                    
                    if (this.formData.description) {
                        this.quillInstance.root.innerHTML = this.formData.description;
                    } else if (this._pendingQuillContent) {
                        this.quillInstance.root.innerHTML = this._pendingQuillContent;
                        this._pendingQuillContent = null;
                    }
                    
                    this.quillInstance.on('text-change', () => {
                        const description = this.quillInstance.root.innerHTML;
                        this.formData.description = description;
                        document.getElementById('modal-description').value = description;
                    });
                } catch (error) {
                    console.error('Quill init error:', error);
                }
            },
            
            handleImageUpload(event) {
                const file = event.target.files[0];
                if (!file) return;
                if (!file.type.startsWith('image/')) { this.showAlert('error', 'Invalid File', 'Please upload a valid image.'); return; }
                if (file.size > 2 * 1024 * 1024) { this.showAlert('error', 'File Too Large', 'Max 2MB.'); return; }
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.formData.image_preview = e.target.result;
                    this.formData.new_image = file;
                    this.formData.new_image_name = file.name;
                    this.formData.current_image = null;
                };
                reader.readAsDataURL(file);
            },
            
            handleBannerUpload(event) {
                const file = event.target.files[0];
                if (!file) return;
                if (!file.type.startsWith('image/')) { this.showAlert('error', 'Invalid File', 'Please upload a valid banner.'); return; }
                if (file.size > 2 * 1024 * 1024) { this.showAlert('error', 'File Too Large', 'Max 2MB.'); return; }
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.formData.banner_preview = e.target.result;
                    this.formData.new_banner = file;
                    this.formData.new_banner_name = file.name;
                    this.formData.current_banner = null;
                };
                reader.readAsDataURL(file);
            },
            
            removeImage() {
                this.formData.image_preview = null;
                this.formData.new_image = null;
                this.formData.new_image_name = '';
                const f = document.getElementById('modal-image-upload');
                if (f) f.value = '';
            },
            
            removeBanner() {
                this.formData.banner_preview = null;
                this.formData.new_banner = null;
                this.formData.new_banner_name = '';
                const f = document.getElementById('modal-banner-upload');
                if (f) f.value = '';
            },
            
            showAlert(type, title, message) {
                window.dispatchEvent(new CustomEvent('show-alert', {
                    detail: { type, title, message }
                }));
            },
            
            submitForm() {
                this.isSubmitting = true;
                this.errorMessage = '';
                
                const form = document.getElementById('event-form');
                const formData = new FormData(form);
                
                // Ensure _method is correct
                formData.set('_method', this.isEditing ? 'PUT' : 'POST');
                
                // Ensure merchant_id is set
                if (this.formData.merchant_id) {
                    formData.set('merchant_id', this.formData.merchant_id);
                }
                
                // Ensure description from Quill
                if (this.quillInstance && this.quillInitialized) {
                    formData.set('description', this.quillInstance.root.innerHTML);
                }
                
                // Ensure dates are set
                formData.set('date', this.formData.date || '');
                if (this.formData.end_date) {
                    formData.set('end_date', this.formData.end_date);
                }
                
                // Compute URL explicitly
                const url = this.isEditing
                    ? '/events/' + this.eventId
                    : '{{ route("events.store") }}';
                
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(async response => {
                    const text = await response.text();
                    
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        throw new Error('Server returned non-JSON: ' + text.substring(0, 300));
                    }
                    
                    if (!response.ok) {
                        let errMsg = data.message || 'HTTP ' + response.status;
                        if (data.errors) {
                            errMsg = Object.entries(data.errors)
                                .map(([field, msgs]) => '• ' + field + ': ' + (Array.isArray(msgs) ? msgs.join(', ') : msgs))
                                .join('\n');
                        }
                        throw new Error(errMsg);
                    }
                    return data;
                })
                .then(data => {
                    this.isSubmitting = false;
                    if (data.success) {
                        this.showAlert('success', 'Success', data.message);
                        this.close();
                        setTimeout(() => {
                            window.location.href = data.redirect || '/events';
                        }, 1000);
                    } else {
                        this.errorMessage = data.message || 'Unknown error';
                        this.showAlert('error', 'Error', data.message || 'Something went wrong.');
                    }
                })
                .catch(error => {
                    this.isSubmitting = false;
                    console.error('Submit error:', error);
                    this.errorMessage = error.message;
                    this.showAlert('error', 'Error', error.message);
                });
            }
        };
    });
});
</script>