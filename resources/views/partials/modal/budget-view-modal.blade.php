{{-- resources/views/partials/modal/budget-view-modal.blade.php --}}

<div x-data="budgetViewModal()" 
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
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="data.name || 'Budget Item'"></h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5" x-text="data.event_name || ''"></p>
                    </div>
                    <button @click="close()" 
                            class="rounded-full bg-gray-100 p-2 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">

                    <!-- Category badge -->
                    <div>
                        <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700 capitalize" x-text="data.category || '—'"></span>
                    </div>

                    <!-- Details -->
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-800">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Details</h3>
                        </div>
                        <dl class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Point of Contact</dt>
                                <dd class="mt-1 text-gray-800 dark:text-white/90" x-text="data.point_of_contact || '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Role</dt>
                                <dd class="mt-1 text-gray-800 dark:text-white/90" x-text="data.role || '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Phone</dt>
                                <dd class="mt-1 text-gray-800 dark:text-white/90" x-text="data.phone || '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Email</dt>
                                <dd class="mt-1 text-gray-800 dark:text-white/90 break-all" x-text="data.email || '—'"></dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Address</dt>
                                <dd class="mt-1 text-gray-800 dark:text-white/90" x-text="data.address || '—'"></dd>
                            </div>
                            <div class="sm:col-span-2" x-show="data.contract_signature">
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Contract Signature</dt>
                                <dd class="mt-1 text-gray-800 dark:text-white/90" x-text="data.contract_signature"></dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Financials -->
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-800">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Financials</h3>
                        </div>
                        <div class="p-5 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Cost</span>
                                <span class="font-medium text-gray-800 dark:text-white/90" x-text="'KES ' + Number(data.cost || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Paid</span>
                                <span class="font-medium text-green-600" x-text="'KES ' + Number(data.paid_amount || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                            </div>
                            <div class="flex justify-between pt-3 border-t border-gray-100 dark:border-gray-800">
                                <span class="text-gray-500 dark:text-gray-400">Balance</span>
                                <span class="font-semibold" 
                                      :class="Number(data.balance || 0) > 0 ? 'text-yellow-600' : 'text-green-600'"
                                      x-text="'KES ' + Number(data.balance || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Status</span>
                                <span class="font-medium text-gray-800 dark:text-white/90 capitalize" x-text="data.status || '—'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Picture -->
                    <div x-show="data.profile_pic_url" class="rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-800">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Profile Picture</h3>
                        </div>
                        <div class="p-5">
                            <img :src="data.profile_pic_url" alt="" class="w-full rounded-lg border border-gray-200 dark:border-gray-700">
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                    <div class="flex justify-end gap-3">
                        <button @click="close()" 
                                type="button"
                                class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            Close
                        </button>
                        <button type="button"
                                @click="switchToEdit()"
                                class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                            Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('budgetViewModal', function() {
        return {
            isOpen: false,
            data: {},
            
            init() {
                window.addEventListener('open-budget-view-modal', (event) => {
                    this.data = event.detail || {};
                    // Add a computed event_name for the header
                    if (this.data.event && this.data.event.name) {
                        this.data.event_name = this.data.event.name;
                    } else if (this.data.event_name === undefined) {
                        this.data.event_name = '';
                    }
                    this.isOpen = true;
                    document.body.style.overflow = 'hidden';
                });
            },
            
            close() {
                this.isOpen = false;
                document.body.style.overflow = '';
            },
            
            switchToEdit() {
                this.close();
                // Small delay so the close animation finishes
                setTimeout(() => {
                    window.dispatchEvent(new CustomEvent('open-budget-edit-modal', { detail: this.data }));
                }, 200);
            }
        };
    });
});
</script>