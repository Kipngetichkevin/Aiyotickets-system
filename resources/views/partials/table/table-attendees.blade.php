{{-- resources/views/partials/table/table-attendees.blade.php --}}
@props(['attendees', 'event'])

<div class="bg-white rounded-xl shadow-sm p-6 mx-4 dark:bg-neutral-800 dark:border-neutral-700" 
     x-data="attendeesTable({
         attendees: {{ json_encode($attendees->toArray()) }},
         eventId: {{ $event->id }}
     })">
    
    <!-- Header with Search and Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Attendees</h2>
            <p class="text-sm text-gray-600 dark:text-neutral-400">Manage event attendees</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
            <!-- Search Bar -->
            <div class="relative">
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3">
                    <svg class="w-4 h-4 text-gray-500 dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                       x-model="searchTerm"
                       @input="filterAttendees()"
                       class="py-2 ps-10 pe-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                       placeholder="Search attendees...">
            </div>
            
            <!-- Filter Dropdown -->
            <select x-model="filterStatus"
                    @change="filterAttendees()"
                    class="py-2 px-3 pe-9 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:focus:ring-neutral-600">
                <option value="all">All Status</option>
                <option value="checked_in">Checked In</option>
                <option value="active">Active</option>
            </select>

            <!-- Export Button -->
            <a href="#" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 dark:bg-transparent dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Export CSV
            </a>
            
            <!-- Add Attendee Button -->
            <button class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700">
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/><path d="M12 5v14"/>
                </svg>
                Add Attendee
            </button>
        </div>
    </div>

    <!-- Results Count -->
    <div class="flex justify-between items-center mb-4 px-1">
        <span class="text-sm text-gray-600 dark:text-neutral-400">
            Showing <span x-text="filteredAttendees.length"></span> of <span x-text="attendees.length"></span> attendees
        </span>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600 dark:text-neutral-400">Rows per page:</span>
            <select x-model="perPage"
                    @change="goToPage(1)"
                    class="py-1 px-2 border border-gray-300 dark:border-neutral-600 rounded-md text-sm bg-white dark:bg-neutral-800 text-gray-700 dark:text-neutral-300">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-800">
                <tr>
                    <th scope="col" class="ps-6 py-3 text-start">
                        <label for="hs-at-with-checkboxes-main" class="flex">
                            <input type="checkbox" 
                                   @change="toggleAll($event)"
                                   class="shrink-0 border-gray-300 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-600 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                        </label>
                    </th>
                    <th scope="col" class="ps-6 lg:ps-3 xl:ps-0 pe-6 py-3 text-start cursor-pointer hover:text-gray-600 dark:hover:text-neutral-300"
                        @click="sortBy('name')">
                        <div class="flex items-center gap-1">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Name</span>
                            <svg class="w-3 h-3" :class="{'opacity-0': sortField !== 'name'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      :d="sortField === 'name' && sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'"/>
                            </svg>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-start cursor-pointer hover:text-gray-600 dark:hover:text-neutral-300"
                        @click="sortBy('email')">
                        <div class="flex items-center gap-1">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Details</span>
                            <svg class="w-3 h-3" :class="{'opacity-0': sortField !== 'email'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      :d="sortField === 'email' && sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'"/>
                            </svg>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-start cursor-pointer hover:text-gray-600 dark:hover:text-neutral-300"
                        @click="sortBy('qr_code')">
                        <div class="flex items-center gap-1">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Status</span>
                            <svg class="w-3 h-3" :class="{'opacity-0': sortField !== 'qr_code'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      :d="sortField === 'qr_code' && sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'"/>
                            </svg>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-start">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Order No.</span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-start cursor-pointer hover:text-gray-600 dark:hover:text-neutral-300"
                        @click="sortBy('total_price')">
                        <div class="flex items-center gap-1">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Total</span>
                            <svg class="w-3 h-3" :class="{'opacity-0': sortField !== 'total_price'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      :d="sortField === 'total_price' && sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'"/>
                            </svg>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-end">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Action</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                <template x-if="paginatedAttendees.length > 0">
                    <template x-for="attendee in paginatedAttendees" :key="attendee.id">
                        <tr>
                            <td class="size-px whitespace-nowrap">
                                <div class="ps-6 py-3">
                                    <input type="checkbox" 
                                           x-model="selectedAttendees" 
                                           :value="attendee.id"
                                           class="shrink-0 border-gray-300 rounded text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-600 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                                </div>
                            </td>
                            <td class="size-px whitespace-nowrap">
                                <div class="ps-6 lg:ps-3 xl:ps-0 pe-6 py-3">
                                    <div class="flex items-center gap-x-3">
                                        <img class="inline-block size-[38px] rounded-full" 
                                             :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(attendee.name)}&background=random`" 
                                             alt="Avatar">
                                        <div class="grow">
                                            <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-200" x-text="attendee.name"></span>
                                            <span class="block text-sm text-gray-500 dark:text-neutral-500" x-text="`Code: ${attendee.transaction_code || 'N/A'}`"></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="h-px w-72 whitespace-nowrap">
                                <div class="px-6 py-3">
                                    <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-200" x-text="attendee.email || 'N/A'"></span>
                                    <span class="block text-sm text-gray-500 dark:text-neutral-500" x-text="attendee.phone || 'N/A'"></span>
                                </div>
                            </td>
                            <td class="size-px whitespace-nowrap">
                                <div class="px-6 py-3">
                                    <span x-show="attendee.qr_code == 1" 
                                          class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs font-medium bg-red-100 text-red-800 rounded-full dark:bg-red-500/10 dark:text-red-500">
                                        <svg class="size-2.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.146-2.854a.5.5 0 1 0-.708-.708L8 7.293 4.854 4.146a.5.5 0 1 0-.708.708L7.293 8l-3.147 3.146a.5.5 0 0 0 .708.708L8 8.707l3.146 3.147a.5.5 0 0 0 .708-.708L8.707 8l3.147-3.146z"/>
                                        </svg>
                                        Checked In
                                    </span>
                                    <span x-show="attendee.qr_code != 1" 
                                          class="py-1 px-1.5 inline-flex items-center gap-x-1 text-xs font-medium bg-teal-100 text-teal-800 rounded-full dark:bg-teal-500/10 dark:text-teal-500">
                                        <svg class="size-2.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 1 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                        </svg>
                                        Active
                                    </span>
                                </div>
                            </td>
                            <td class="size-px whitespace-nowrap">
                                <div class="px-6 py-3">
                                    <span class="text-sm text-gray-500 dark:text-neutral-500" 
                                          x-text="`{{ $event->id }}-${attendee.ticket_id}-${attendee.id}`"></span>
                                </div>
                            </td>
                            <td class="size-px whitespace-nowrap">
                                <div class="px-6 py-3">
                                    <span class="text-sm text-gray-500 dark:text-neutral-500" 
                                          x-text="`{{ $event->currency ?? 'KES' }} ${Number(attendee.total_price || 0).toFixed(2)}`"></span>
                                </div>
                            </td>
                            <td class="size-px whitespace-nowrap">
                                <div class="px-6 py-3 text-end">
                                    <a :href="`/ticket/pdf/${attendee.id}`" 
                                       class="py-2 px-3 inline-flex items-center gap-x-2 rounded-md border border-gray-200 font-semibold bg-white text-gray-700 shadow-sm hover:bg-gray-50 text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700">
                                        Generate PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>
                </template>
                <template x-if="paginatedAttendees.length === 0">
                    <tr>
                        <td colspan="7" class="text-center py-4 text-gray-500 dark:text-neutral-400">
                            <span x-text="searchTerm ? 'No attendees found matching your search.' : 'No attendees registered for this event.'"></span>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-6 px-1">
        <div>
            <span class="text-sm text-gray-600 dark:text-neutral-400">
                Showing <span x-text="((currentPage - 1) * perPage) + 1"></span> 
                to <span x-text="Math.min(currentPage * perPage, filteredAttendees.length)"></span> 
                of <span x-text="filteredAttendees.length"></span> entries
            </span>
        </div>
        <div class="flex items-center gap-2">
            <button @click="previousPage()" 
                    :disabled="currentPage === 1"
                    class="py-1.5 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-transparent dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Previous
            </button>
            
            <div class="flex items-center gap-1">
                <template x-for="page in totalPages" :key="page">
                    <button @click="goToPage(page)"
                            class="py-1.5 px-3 min-w-[2.5rem] inline-flex justify-center items-center text-sm font-medium rounded-lg border"
                            :class="currentPage === page ? 'border-blue-600 bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:border-blue-500 dark:text-blue-500' : 'border-gray-200 bg-white text-gray-800 hover:bg-gray-50 dark:bg-transparent dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                            x-text="page">
                    </button>
                </template>
            </div>
            
            <button @click="nextPage()" 
                    :disabled="currentPage === totalPages || totalPages === 0"
                    class="py-1.5 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-transparent dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800">
                Next
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
    function attendeesTable({ attendees, eventId }) {
        return {
            // Data
            attendees: attendees,
            eventId: eventId,
            
            // Filters
            searchTerm: '',
            filterStatus: 'all',
            
            // Sorting
            sortField: 'name',
            sortDirection: 'asc',
            
            // Pagination
            currentPage: 1,
            perPage: 10,
            
            // Selection
            selectedAttendees: [],
            
            // Computed properties
            get filteredAttendees() {
                let filtered = this.attendees;
                
                // Search filter
                if (this.searchTerm) {
                    const search = this.searchTerm.toLowerCase();
                    filtered = filtered.filter(a => 
                        a.name?.toLowerCase().includes(search) ||
                        a.email?.toLowerCase().includes(search) ||
                        a.phone?.toLowerCase().includes(search) ||
                        a.transaction_code?.toLowerCase().includes(search)
                    );
                }
                
                // Status filter
                if (this.filterStatus === 'checked_in') {
                    filtered = filtered.filter(a => a.qr_code == 1);
                } else if (this.filterStatus === 'active') {
                    filtered = filtered.filter(a => a.qr_code != 1);
                }
                
                // Sorting
                if (this.sortField) {
                    filtered.sort((a, b) => {
                        let valA = a[this.sortField] || '';
                        let valB = b[this.sortField] || '';
                        
                        if (typeof valA === 'string') {
                            valA = valA.toLowerCase();
                            valB = valB.toLowerCase();
                        }
                        
                        if (valA < valB) return this.sortDirection === 'asc' ? -1 : 1;
                        if (valA > valB) return this.sortDirection === 'asc' ? 1 : -1;
                        return 0;
                    });
                }
                
                return filtered;
            },
            
            get paginatedAttendees() {
                const start = (this.currentPage - 1) * this.perPage;
                const end = start + this.perPage;
                return this.filteredAttendees.slice(start, end);
            },
            
            get totalPages() {
                return Math.ceil(this.filteredAttendees.length / this.perPage) || 1;
            },
            
            // Methods
            filterAttendees() {
                this.goToPage(1);
            },
            
            sortBy(field) {
                if (this.sortField === field) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortField = field;
                    this.sortDirection = 'asc';
                }
                // Reset to first page when sorting
                this.goToPage(1);
            },
            
            goToPage(page) {
                if (page >= 1 && page <= this.totalPages) {
                    this.currentPage = page;
                }
            },
            
            previousPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                }
            },
            
            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                }
            },
            
            toggleAll(event) {
                if (event.target.checked) {
                    this.selectedAttendees = this.paginatedAttendees.map(a => a.id);
                } else {
                    this.selectedAttendees = [];
                }
            },
            
            // Lifecycle hooks
            init() {
                console.log('Attendees table initialized with', this.attendees.length, 'attendees');
            }
        }
    }
</script>