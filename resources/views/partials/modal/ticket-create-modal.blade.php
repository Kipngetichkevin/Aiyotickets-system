{{-- resources/views/partials/modal/ticket-create-modal.blade.php --}}
@props(['eventId' => null])

<div x-data="ticketCreateModal()" 
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
    
    <!-- Slideover Panel -->
    <div class="fixed inset-y-0 right-0 flex max-w-full">
        <div class="relative w-screen max-w-2xl transform transition-transform duration-300 ease-in-out"
             :class="isOpen ? 'translate-x-0' : 'translate-x-full'">
            
            <div class="flex h-full flex-col overflow-y-auto bg-white shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="modalTitle"></h2>
                    <button @click="close()" 
                            class="rounded-full bg-gray-100 p-2 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form Body -->
                <div class="flex-1 overflow-y-auto px-6 py-6">
                    <form id="ticket-form" 
                          @submit.prevent="submitForm()"
                          enctype="multipart/form-data"
                          novalidate>
                        @csrf
                        <input type="hidden" name="_method" x-model="formMethod">
                        <input type="hidden" name="event_id" x-model="eventId">

                        <div class="space-y-6">
                            <!-- Ticket Number (Read-only, shown only when editing) -->
                            <div x-show="isEditing" x-transition>
                                <label for="ticket-no" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Ticket Number <span class="text-gray-400 text-xs">(Auto-generated)</span>
                                </label>
                                <input type="text" id="ticket-no" 
                                    x-model="formData.ticket_no"
                                    disabled
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-not-allowed opacity-70">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ticket numbers are automatically generated and cannot be changed.</p>
                            </div>

                            <!-- Ticket Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Ticket Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" required
                                    x-model="formData.name"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="Enter ticket name">
                            </div>

                            <!-- Price, Currency & Quantity -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Price <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="price" required step="0.01" min="0"
                                        x-model="formData.price"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                        placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Currency <span class="text-red-500">*</span>
                                    </label>
                                    <select name="currency" required
                                        x-model="formData.currency"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        <option value="KES">KES - Kenyan Shilling</option>
                                        <option value="USD">USD - US Dollar</option>
                                        <option value="USDT">USDT - Tether</option>
                                        <option value="EUROS">EUROS - Euro</option>
                                        <option value="POUND">POUND - British Pound</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Quantity <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="quantity" required min="1"
                                        x-model="formData.quantity"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                        placeholder="100">
                                </div>
                            </div>

                            <!-- Start & End Date -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Start Date
                                    </label>
                                    <input type="date" 
                                           name="start_date" 
                                           x-model="formData.start_date"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">When ticket sales begin</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        End Date
                                    </label>
                                    <input type="date" 
                                           name="end_date" 
                                           x-model="formData.end_date"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">When ticket sales end</p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                    <div class="flex justify-end gap-3">
                        <button @click="close()" 
                                type="button"
                                class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            Cancel
                        </button>
                        <button type="submit" 
                                form="ticket-form"
                                :disabled="isSubmitting"
                                class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-text="isSubmitting ? 'Saving...' : submitButtonText"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('ticketCreateModal', function() {
        return {
            isOpen: false,
            modalTitle: 'Create New Ticket',
            submitButtonText: 'Create Ticket',
            formAction: '{{ route("tickets.store") }}',
            formMethod: 'POST',
            isEditing: false,
            isSubmitting: false,
            ticketId: null,
            eventId: {{ $eventId ?? 'null' }},
            formData: {
                name: '',
                price: '',
                currency: 'KES',
                quantity: '',
                start_date: '',
                end_date: '',
                ticket_no: ''
            },
            
            init() {
                // Listen for create ticket event
                window.addEventListener('open-ticket-create-modal', (event) => {
                    this.eventId = event.detail?.eventId || {{ $eventId ?? 'null' }};
                    this.resetForm();
                    this.isEditing = false;
                    this.modalTitle = 'Create New Ticket';
                    this.submitButtonText = 'Create Ticket';
                    this.formAction = '{{ route("tickets.store") }}';
                    this.formMethod = 'POST';
                    this.ticketId = null;
                    console.log('🎫 Opening create ticket modal for event:', this.eventId);
                    this.open();
                });
                
                // Listen for edit ticket event
                window.addEventListener('open-ticket-edit-modal', (event) => {
                    const ticketData = event.detail;
                    console.log('📝 Edit ticket data received:', ticketData);
                    
                    if (!ticketData || !ticketData.id) {
                        console.error('❌ Invalid ticket data for edit:', ticketData);
                        this.showAlert('error', 'Error', 'Invalid ticket data');
                        return;
                    }
                    
                    this.loadTicketData(ticketData);
                    this.isEditing = true;
                    this.ticketId = ticketData.id;
                    this.eventId = ticketData.event_id;
                    this.modalTitle = `Edit Ticket: ${ticketData.name || 'Untitled'}`;
                    this.submitButtonText = 'Update Ticket';
                    // ✅ FIX: Correct URL for update
                    this.formAction = `/tickets/${ticketData.id}`;
                    this.formMethod = 'PUT';
                    console.log('🎯 Form action set to:', this.formAction);
                    this.open();
                });
            },
            
            loadTicketData(ticket) {
                const formatDateForInput = (dateStr) => {
                    if (!dateStr) return '';
                    try {
                        const date = new Date(dateStr);
                        if (isNaN(date.getTime())) return '';
                        return date.toISOString().split('T')[0];
                    } catch (e) {
                        return '';
                    }
                };
                
                this.formData = {
                    name: ticket.name || '',
                    price: ticket.price || '',
                    currency: ticket.currency || 'KES',
                    quantity: ticket.quantity || '',
                    start_date: formatDateForInput(ticket.start_date),
                    end_date: formatDateForInput(ticket.end_date),
                    ticket_no: ticket.ticket_no || ''
                };
                
                console.log('📋 Form data loaded:', JSON.stringify(this.formData));
            },
            
            resetForm() {
                this.formData = {
                    name: '',
                    price: '',
                    currency: 'KES',
                    quantity: '',
                    start_date: '',
                    end_date: '',
                    ticket_no: ''
                };
                this.ticketId = null;
            },
            
            open() {
                this.isOpen = true;
                document.body.style.overflow = 'hidden';
            },
            
            close() {
                this.isOpen = false;
                document.body.style.overflow = '';
                this.resetForm();
            },
            
            showAlert(type, title, message) {
                window.dispatchEvent(new CustomEvent('show-alert', {
                    detail: { type, title, message }
                }));
            },
            
            submitForm() {
                console.log('🚀 Submitting form...');
                console.log('📊 Current form data:', JSON.stringify(this.formData));
                console.log('📌 Editing mode:', this.isEditing);
                console.log('🆔 Ticket ID:', this.ticketId);
                console.log('🔗 Form action:', this.formAction);
                
                // Validate
                if (!this.formData.name || this.formData.name.trim() === '') {
                    this.showAlert('error', 'Validation Error', 'Ticket name is required');
                    return;
                }
                
                if (this.formData.price === '' || parseFloat(this.formData.price) < 0) {
                    this.showAlert('error', 'Validation Error', 'Price must be greater than or equal to 0');
                    return;
                }
                
                if (!this.formData.quantity || parseInt(this.formData.quantity) < 1) {
                    this.showAlert('error', 'Validation Error', 'Quantity must be at least 1');
                    return;
                }
                
                this.isSubmitting = true;
                
                // Build FormData
                const formData = new FormData();
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                
                formData.append('_token', csrfToken);
                formData.append('event_id', this.eventId);
                formData.append('name', this.formData.name);
                formData.append('price', this.formData.price);
                formData.append('currency', this.formData.currency);
                formData.append('quantity', this.formData.quantity);
                formData.append('start_date', this.formData.start_date || '');
                formData.append('end_date', this.formData.end_date || '');
                
                // For update, we need to spoof PUT method
                if (this.isEditing) {
                    formData.append('_method', 'PUT');
                }
                
                console.log('📤 FormData being sent:');
                for (let [key, value] of formData.entries()) {
                    console.log(`  ${key}: ${value}`);
                }
                
                // Determine URL
                let url;
                if (this.isEditing && this.ticketId) {
                    url = `/tickets/${this.ticketId}`;
                } else {
                    url = '{{ route("tickets.store") }}';
                }
                console.log('🌐 Sending request to:', url);
                
                fetch(url, {
                    method: 'POST', // Always POST with _method for PUT
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(async response => {
                    // Log the raw response for debugging
                    const text = await response.text();
                    console.log('📄 Raw response:', text);
                    
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('❌ Failed to parse JSON:', e);
                        throw new Error('Invalid JSON response from server. Response: ' + text.substring(0, 200));
                    }
                    
                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            const errorMessages = Object.values(data.errors).flat().join('\n');
                            throw new Error('Validation failed:\n' + errorMessages);
                        }
                        throw new Error(data.message || data.error || `HTTP error ${response.status}`);
                    }
                    return data;
                })
                .then(data => {
                    this.isSubmitting = false;
                    console.log('✅ Response data:', data);
                    
                    if (data.success) {
                        this.showAlert('success', 'Success', data.message || 'Ticket saved successfully!');
                        // Dispatch event to refresh ticket list
                        window.dispatchEvent(new CustomEvent('ticket-saved', {
                            detail: { ticket: data.data, action: this.isEditing ? 'updated' : 'created' }
                        }));
                        this.close();
                        // Reload the page to show updated data
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        this.showAlert('error', 'Error', data.message || 'Something went wrong.');
                    }
                })
                .catch(error => {
                    this.isSubmitting = false;
                    console.error('❌ Error:', error);
                    this.showAlert('error', 'Error', error.message || 'An unexpected error occurred.');
                });
            }
        };
    });
});
</script>