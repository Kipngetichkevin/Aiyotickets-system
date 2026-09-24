{{-- resources/views/partials/chart/tickets-chart.blade.php --}}

<div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Tickets Overview</h3>
        <span class="text-xs text-gray-500 dark:text-gray-400">All time</span>
    </div>

    @php
        $soldCount   = $ticketsSold ?? 0;
        $orderCount  = $totalOrders ?? 0;
        $avgPerOrder = $orderCount > 0 ? round($soldCount / $orderCount, 1) : 0;
    @endphp

    <div class="grid grid-cols-2 gap-4">
        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900/50">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Sold</p>
            <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $soldCount }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">tickets</p>
        </div>

        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900/50">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Orders</p>
            <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $orderCount }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">total</p>
        </div>

        <div class="col-span-2 rounded-lg bg-gray-50 p-4 dark:bg-gray-900/50">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Avg per Order</p>
            <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $avgPerOrder }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">tickets per order</p>
        </div>
    </div>
</div>