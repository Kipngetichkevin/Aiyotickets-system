{{-- resources/views/partials/chart/income-chart.blade.php --}}

<div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Income Overview</h3>
        <span class="text-xs text-gray-500 dark:text-gray-400">All time</span>
    </div>

    @php
        $incomeTotal  = $totalSales   ?? 0;
        $pendingTotal = $pendingSales ?? 0;
        $grandTotal   = $incomeTotal + $pendingTotal;
        $paidPercent    = $grandTotal > 0 ? round(($incomeTotal / $grandTotal) * 100) : 0;
        $pendingPercent = $grandTotal > 0 ? round(($pendingTotal / $grandTotal) * 100) : 0;
    @endphp

    <div class="space-y-5">
        <!-- Paid Sales -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">Paid Sales</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                    KES {{ number_format($incomeTotal, 2) }}
                </span>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                <div class="h-full rounded-full bg-green-500 transition-all"
                     style="width: {{ $paidPercent }}%;"></div>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $paidPercent }}% of total</p>
        </div>

        <!-- Pending Sales -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">Pending Sales</span>
                <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                    KES {{ number_format($pendingTotal, 2) }}
                </span>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                <div class="h-full rounded-full bg-yellow-500 transition-all"
                     style="width: {{ $pendingPercent }}%;"></div>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $pendingPercent }}% of total</p>
        </div>

        <!-- Total -->
        <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500 dark:text-gray-400">Total</span>
                <span class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    KES {{ number_format($grandTotal, 2) }}
                </span>
            </div>
        </div>
    </div>
</div>