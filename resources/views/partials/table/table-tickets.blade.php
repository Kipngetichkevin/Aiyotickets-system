{{-- resources/views/partials/table/table-tickets.blade.php --}}
@props(['tickets', 'eventId', 'soldQuantities' => []])

<div x-data="ticketsTable()" 
     x-init="init()"
     class="bg-white rounded-2xl shadow-sm p-4 sm:p-6 mb-8 mx-4 dark:bg-neutral-800 dark:border-neutral-700 border border-gray-100 dark:border-neutral-700">
    
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
                Ticket Types
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your event tickets and pricing</p>
        </div>
        <button @click="$dispatch('open-ticket-create-modal', { eventId: {{ $eventId }} })" 
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-lg shadow-blue-600/25 hover:shadow-blue-600/40 transform hover:scale-[1.02] active:scale-[0.98] whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add New Ticket
        </button>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-12">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
            <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        <p class="text-gray-500 dark:text-gray-400 font-medium">Loading tickets...</p>
    </div>

    <!-- Tickets Grid -->
    <div x-show="!loading">
        @if($tickets->isEmpty())
            <div class="text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400 font-medium">No tickets available for this event</p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Create your first ticket to start selling</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-5">
                @foreach($tickets as $ticket)
                    @php
                        $soldCount = $soldQuantities[$ticket->id] ?? 0;
                        $percentage = $ticket->quantity > 0 ? min(100, ($soldCount / $ticket->quantity) * 100) : 0;
                        $isLocked = $soldCount > 0;
                    @endphp
                    <div class="group relative bg-white dark:bg-neutral-800/50 rounded-2xl border border-gray-200 dark:border-neutral-700 hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-300 hover:shadow-xl hover:shadow-blue-500/5 hover:-translate-y-1 overflow-hidden ticket-card">
                        <!-- Progress Bar -->
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gray-200 dark:bg-neutral-700 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-purple-600 transition-all duration-1000" 
                                 style="width: {{ $percentage }}%">
                            </div>
                        </div>
                        
                        <!-- Status Badge -->
                        <div class="absolute top-4 right-4 z-10">
                            @if($isLocked)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 rounded-full">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Locked
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-full">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                    Active
                                </span>
                            @endif
                        </div>

                        <!-- Card Content -->
                        <div class="p-4 md:p-5 pt-6">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1 pr-14">
                                    <h3 class="text-base md:text-lg font-semibold text-gray-800 dark:text-neutral-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-1">
                                        {{ $ticket->name }}
                                        @if($isLocked)
                                            <span class="inline-block ml-1 text-amber-500" title="This ticket has sales and cannot be deleted">
                                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                            </span>
                                        @endif
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">#{{ $ticket->ticket_no ?? $ticket->id }}</p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $ticket->currency ?? 'KES' }}</span>
                                    <span class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($ticket->price, 0) }}</span>
                                </div>
                            </div>

                            <!-- Stats -->
                            <div class="grid grid-cols-3 gap-1.5 md:gap-2 mt-4">
                                <div class="bg-gray-50 dark:bg-neutral-700/50 rounded-xl p-2 text-center">
                                    <p class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">Total</p>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ $ticket->quantity }}</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-neutral-700/50 rounded-xl p-2 text-center">
                                    <p class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">Sold</p>
                                    <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ $soldCount }}</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-neutral-700/50 rounded-xl p-2 text-center">
                                    <p class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">Available</p>
                                    <p class="text-sm font-semibold text-blue-600 dark:text-blue-400">{{ $ticket->quantity - $soldCount }}</p>
                                </div>
                            </div>

                            <!-- Dates -->
                            @if($ticket->start_date || $ticket->end_date)
                                <div class="mt-3 md:mt-4 pt-3 md:pt-4 border-t border-gray-100 dark:border-neutral-700">
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-1 text-[10px] md:text-xs text-gray-500 dark:text-gray-400">
                                        @if($ticket->start_date)
                                            <div class="flex items-center gap-1.5">
                                                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span class="truncate">From {{ \Carbon\Carbon::parse($ticket->start_date)->format('d M Y') }}</span>
                                            </div>
                                        @endif
                                        @if($ticket->end_date)
                                            <div class="flex items-center gap-1.5">
                                                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span class="truncate">Until {{ \Carbon\Carbon::parse($ticket->end_date)->format('d M Y') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Actions -->
                            <div class="mt-3 md:mt-5 pt-3 md:pt-4 border-t border-gray-100 dark:border-neutral-700 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 md:gap-2">
                                    <button @click="$dispatch('open-ticket-edit-modal', { 
                                        id: {{ $ticket->id }},
                                        event_id: {{ $eventId }},
                                        name: '{{ addslashes($ticket->name) }}',
                                        price: {{ $ticket->price }},
                                        quantity: {{ $ticket->quantity }},
                                        start_date: '{{ $ticket->start_date }}',
                                        end_date: '{{ $ticket->end_date }}'
                                    })" 
                                            class="inline-flex items-center justify-center gap-1 px-2.5 py-1.5 text-xs md:text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 rounded-lg transition-all duration-200 hover:scale-105 flex-1 sm:flex-none">
                                        <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                        <span class="hidden sm:inline">Edit</span>
                                    </button>
                                    
                                    @if($isLocked)
                                        <button disabled
                                                class="inline-flex items-center justify-center gap-1 px-2.5 py-1.5 text-xs md:text-sm font-medium text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-gray-800 rounded-lg cursor-not-allowed flex-1 sm:flex-none"
                                                title="This ticket has sales and cannot be deleted">
                                            <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            <span class="hidden sm:inline">Locked</span>
                                        </button>
                                    @else
                                        <button @click="$dispatch('open-delete-ticket-modal', { ticketId: {{ $ticket->id }}, ticketName: '{{ addslashes($ticket->name) }}' })" 
                                                class="inline-flex items-center justify-center gap-1 px-2.5 py-1.5 text-xs md:text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/30 rounded-lg transition-all duration-200 hover:scale-105 flex-1 sm:flex-none">
                                            <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            <span class="hidden sm:inline">Delete</span>
                                        </button>
                                    @endif
                                </div>
                                
                                <div class="text-center sm:text-right">
                                    <span class="text-[10px] md:text-xs font-medium text-gray-500 dark:text-gray-400">
                                        {{ $percentage }}% sold
                                    </span>
                                </div>
                            </div>

                            @if($isLocked)
                                <div class="mt-2 text-[10px] text-amber-600 dark:text-amber-400 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Locked: {{ $soldCount }} ticket(s) sold</span>
                                </div>
                            @endif
                        </div>

                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/0 to-purple-500/0 group-hover:from-blue-500/[0.03] group-hover:to-purple-500/[0.03] transition-all duration-300 pointer-events-none rounded-2xl"></div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Ticket Modals -->
@include('partials.modal.ticket-create-modal', ['eventId' => $eventId])
@include('partials.modal.ticket-delete-modal')
@include('partials.modal.alert-modal')

<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('ticketsTable', function() {
        return {
            loading: false,
            
            init() {
                // Listen for tickets updated event
                window.addEventListener('tickets-updated', () => {
                    this.refreshTickets();
                });
                
                // Listen for delete success
                window.addEventListener('ticket-deleted', () => {
                    this.refreshTickets();
                });
            },
            
            refreshTickets() {
                this.loading = true;
                
                // Fetch fresh ticket data
                fetch(`/events/{{ $eventId }}/tickets`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch tickets');
                    }
                    return response.text();
                })
                .then(html => {
                    // Replace the tickets grid content
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.querySelector('.grid');
                    
                    if (newContent) {
                        const currentGrid = document.querySelector('.grid');
                        if (currentGrid) {
                            currentGrid.parentNode.replaceChild(newContent, currentGrid);
                        }
                    }
                    this.loading = false;
                })
                .catch(error => {
                    console.error('Error refreshing tickets:', error);
                    this.loading = false;
                    // Fallback: reload the page
                    window.location.reload();
                });
            }
        };
    });
});
</script>

<style>
    /* Card entrance animation */
    .ticket-card {
        animation: fadeInUp 0.5s ease-out both;
    }
    
    .ticket-card:nth-child(1) { animation-delay: 0.03s; }
    .ticket-card:nth-child(2) { animation-delay: 0.06s; }
    .ticket-card:nth-child(3) { animation-delay: 0.09s; }
    .ticket-card:nth-child(4) { animation-delay: 0.12s; }
    .ticket-card:nth-child(5) { animation-delay: 0.15s; }
    .ticket-card:nth-child(6) { animation-delay: 0.18s; }
    .ticket-card:nth-child(7) { animation-delay: 0.21s; }
    .ticket-card:nth-child(8) { animation-delay: 0.24s; }
    .ticket-card:nth-child(9) { animation-delay: 0.27s; }
    .ticket-card:nth-child(10) { animation-delay: 0.30s; }
    .ticket-card:nth-child(11) { animation-delay: 0.33s; }
    .ticket-card:nth-child(12) { animation-delay: 0.36s; }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px) scale(0.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    /* Pulse animation for status dots */
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>