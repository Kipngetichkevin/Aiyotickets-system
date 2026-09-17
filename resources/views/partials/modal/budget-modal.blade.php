{{-- resources/views/partials/modal/budget-modal.blade.php --}}

<div x-data="budgetModal()" 
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
    
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="close()"></div>
    
    <div class="fixed inset-y-0 right-0 flex max-w-full">
        <div class="relative w-screen max-w-2xl transform transition-transform duration-300 ease-in-out"
             :class="isOpen ? 'translate-x-0' : 'translate-x-full'">
            
            <div class="flex h-full flex-col overflow-y-auto bg-white shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="modalTitle">
                        Add Budget Item
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
                    <form id="budget-form" 
                          method="POST" 
                          :action="formAction" 
                          enctype="multipart/form-data"
                          @submit.prevent="submitForm">
                        @csrf
                        <input type="hidden" name="_method" x-model="formMethod">

                        <div class="space-y-6">
                            <!-- Event -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Event <span class="text-red-500">*</span>
                                </label>
                                <select name="event_id" required x-model="formData.event_id"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">— Select event —</option>
                                    @foreach($events as $ev)
                                        <option value="{{ $ev->id }}">{{ $ev->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" required x-model="formData.name"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="e.g. Sound System Rental">
                            </div>

                            <!-- Category & Role -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Category <span class="text-red-500">*</span>
                                    </label>
                                    <select name="category" required x-model="formData.category"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        <option value="supplier">Supplier</option>
                                        <option value="personnel">Personnel</option>
                                        <option value="debtor">Debtor</option>
                                        <option value="creditor">Creditor</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                                    <input type="text" name="role" x-model="formData.role"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                        placeholder="e.g. Vendor">
                                </div>
                            </div>

                            <!-- Point of Contact & Phone -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Point of Contact</label>
                                    <input type="text" name="point_of_contact" x-model="formData.point_of_contact"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                                    <input type="text" name="phone" x-model="formData.phone"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <input type="email" name="email" x-model="formData.email"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>

                            <!-- Address -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                                <input type="text" name="address" x-model="formData.address"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>

                            <!-- Contract Signature -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contract Signature</label>
                                <input type="text" name="contract_signature" x-model="formData.contract_signature"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>

                            <!-- Cost, Paid, Status -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cost (KES)</label>
                                    <input type="number" step="0.01" name="cost" x-model="formData.cost"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                        placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Paid (KES)</label>
                                    <input type="number" step="0.01" name="paid_amount" x-model="formData.paid_amount"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                        placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                    <select name="status" x-model="formData.status"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        <option value="pending">Pending</option>
                                        <option value="partial">Partial</option>
                                        <option value="paid">Paid</option>
                                        <option value="overdue">Overdue</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Profile Picture -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Profile Picture</label>
                                <div class="mt-1 flex justify-center rounded-lg border-2 border-dashed border-gray-300 px-6 py-6 dark:border-gray-700">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="mt-2 flex justify-center text-sm leading-6 text-gray-600 dark:text-gray-400">
                                            <label for="budget-image-upload" class="relative cursor-pointer rounded-md bg-white font-semibold text-blue-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-600 focus-within:ring-offset-2 hover:text-blue-500 dark:bg-gray-800">
                                                <span>Upload a file</span>
                                                <input id="budget-image-upload" name="profile_pic" type="file" accept="image/*" 
                                                    class="sr-only"
                                                    @change="handleImageUpload($event)">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs leading-5 text-gray-600 dark:text-gray-400">PNG, JPG up to 2MB</p>
                                    </div>
                                </div>
                                
                                <div class="mt-3" x-show="formData.image_preview || formData.current_image">
                                    <div class="relative inline-block">
                                        <img :src="formData.image_preview || formData.current_image" 
                                             class="h-32 w-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700" 
                                             alt="Budget image">
                                        <button type="button" 
                                                @click="removeImage()"
                                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1" x-show="formData.new_image" x-text="formData.new_image_name"></p>
                                    <p class="text-xs text-gray-500 mt-1" x-show="!formData.new_image && formData.current_image">Current image</p>
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
                                form="budget-form"
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

<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('budgetModal', function() {
        return {
            isOpen: false,
            modalTitle: 'Add Budget Item',
            submitButtonText: 'Create',
            formAction: '{{ route("budgets.store") }}',
            formMethod: 'POST',
            isEditing: false,
            isSubmitting: false,
            budgetId: null,
            formData: {
                event_id: '',
                name: '',
                category: 'supplier',
                role: '',
                point_of_contact: '',
                phone: '',
                email: '',
                address: '',
                contract_signature: '',
                cost: 0,
                paid_amount: 0,
                status: 'pending',
                current_image: null,
                image_preview: null,
                new_image: null,
                new_image_name: ''
            },
            
            init() {
                window.addEventListener('open-budget-create-modal', (event) => {
                    this.resetForm();
                    this.isEditing = false;
                    this.modalTitle = 'Add Budget Item';
                    this.submitButtonText = 'Create';
                    this.formAction = '{{ route("budgets.store") }}';
                    this.formMethod = 'POST';

                    // Pre-select category if passed from the trigger
                    if (event.detail && event.detail.category) {
                        this.formData.category = event.detail.category;
                    }

                    this.open();
                });
                
                window.addEventListener('open-budget-edit-modal', (event) => {
                    const data = event.detail;
                    this.loadBudgetData(data);
                    this.isEditing = true;
                    this.budgetId = data.id;
                    this.modalTitle = 'Edit Budget Item';
                    this.submitButtonText = 'Save Changes';
                    this.formAction = `/budgets/${data.id}`;
                    this.formMethod = 'PUT';
                    this.open();
                });
            },
            
            loadBudgetData(b) {
                const getImageUrl = (path) => {
                    if (!path) return null;
                    if (path.startsWith('http://') || path.startsWith('https://')) return path;
                    if (path.startsWith('/storage/')) return path;
                    if (path.startsWith('storage/')) return '/' + path;
                    return '/storage/' + path;
                };
                
                this.formData = {
                    event_id: b.event_id || '',
                    name: b.name || '',
                    category: b.category || 'supplier',
                    role: b.role || '',
                    point_of_contact: b.point_of_contact || '',
                    phone: b.phone || '',
                    email: b.email || '',
                    address: b.address || '',
                    contract_signature: b.contract_signature || '',
                    cost: b.cost || 0,
                    paid_amount: b.paid_amount || 0,
                    status: b.status || 'pending',
                    current_image: getImageUrl(b.profile_pic),
                    image_preview: null,
                    new_image: null,
                    new_image_name: ''
                };
            },
            
            resetForm() {
                this.formData = {
                    event_id: '',
                    name: '',
                    category: 'supplier',
                    role: '',
                    point_of_contact: '',
                    phone: '',
                    email: '',
                    address: '',
                    contract_signature: '',
                    cost: 0,
                    paid_amount: 0,
                    status: 'pending',
                    current_image: null,
                    image_preview: null,
                    new_image: null,
                    new_image_name: ''
                };
                this.budgetId = null;
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
            
            handleImageUpload(event) {
                const file = event.target.files[0];
                if (file) {
                    if (!file.type.startsWith('image/')) {
                        alert('Please upload a valid image file.');
                        return;
                    }
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Image must be less than 2MB.');
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.formData.image_preview = e.target.result;
                        this.formData.new_image = file;
                        this.formData.new_image_name = file.name;
                        this.formData.current_image = null;
                    };
                    reader.readAsDataURL(file);
                }
            },
            
            removeImage() {
                this.formData.image_preview = null;
                this.formData.new_image = null;
                this.formData.new_image_name = '';
                this.formData.current_image = null;
                const fileInput = document.getElementById('budget-image-upload');
                if (fileInput) fileInput.value = '';
            },
            
            submitForm() {
                this.isSubmitting = true;
                
                const form = document.getElementById('budget-form');
                const formData = new FormData(form);
                
                if (this.isEditing && this.budgetId) {
                    formData.append('_method', 'PUT');
                }
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                
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
                        throw new Error('Invalid response from server');
                    }
                    
                    if (!response.ok) {
                        throw new Error(data.message || `HTTP error ${response.status}`);
                    }
                    return data;
                })
                .then(data => {
                    this.isSubmitting = false;
                    
                    if (data.success) {
                        this.close();
                        setTimeout(() => window.location.reload(), 500);
                    } else {
                        alert(data.message || 'Something went wrong.');
                    }
                })
                .catch(error => {
                    this.isSubmitting = false;
                    console.error('Error:', error);
                    alert(error.message || 'An unexpected error occurred.');
                });
            }
        };
    });
});
</script>