{{-- ==========================================================
     Charts — Income (bar) + Tickets (area)
     Data from DashboardController: $revenueByMonth, $ticketsByMonth, $monthLabels
     Style: Preline cards + ApexCharts
     ========================================================== --}}

<div class="p-0 space-y-4 sm:space-y-6" style="margin-bottom: 24px;">
    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px;" class="charts-grid">

        {{-- ============ INCOME BAR CHART ============ --}}
        <div class="p-4 md:p-5 min-h-[410px] flex flex-col bg-white border shadow-sm rounded-xl" style="border-color: #e5e7eb;">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-sm text-gray-500" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin: 0 0 4px 0;">
                        Income (Last 12 months)
                    </h2>
                    <p class="text-xl sm:text-2xl font-medium text-gray-800" style="font-size: 22px; font-weight: 700; color: #111827; margin: 0;">
                        KES {{ number_format($totalRevenue, 0) }}
                    </p>
                </div>
                <div>
                    <span class="py-[5px] px-1.5 inline-flex items-center gap-x-1 text-xs font-medium rounded-md" style="background: #d1fae5; color: #065f46; padding: 5px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                        <svg class="inline-block" width="12" height="12" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14" />
                            <path d="m19 12-7 7-7-7" />
                        </svg>
                        12 mo
                    </span>
                </div>
            </div>
            <div id="chart-income" style="margin-top: 12px;"></div>
        </div>

        {{-- ============ TICKETS AREA CHART ============ --}}
        <div class="p-4 md:p-5 min-h-[410px] flex flex-col bg-white border shadow-sm rounded-xl" style="border-color: #e5e7eb;">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-sm text-gray-500" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin: 0 0 4px 0;">
                        Tickets (Last 12 months)
                    </h2>
                    <p class="text-xl sm:text-2xl font-medium text-gray-800" style="font-size: 22px; font-weight: 700; color: #111827; margin: 0;">
                        {{ number_format($totalTicketsSold) }} tickets
                    </p>
                </div>
                <div>
                    <span class="py-[5px] px-1.5 inline-flex items-center gap-x-1 text-xs font-medium rounded-md" style="background: #dbeafe; color: #1e40af; padding: 5px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                        <svg class="inline-block" width="12" height="12" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 19V5" />
                            <path d="m5 12 7-7 7 7" />
                        </svg>
                        12 mo
                    </span>
                </div>
            </div>
            <div id="chart-tickets" style="margin-top: 12px;"></div>
        </div>

    </div>
</div>

<style>
    @media (max-width: 1024px) {
        .charts-grid { grid-template-columns: 1fr !important; }
    }
</style>

{{-- Charts JS — pushed to @stack('scripts') in dashboard --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    window.addEventListener('load', () => {

        // ============ INCOME BAR CHART ============
        const incomeEl = document.querySelector('#chart-income');
        if (incomeEl) {
            new ApexCharts(incomeEl, {
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    fontFamily: 'Inter, ui-sans-serif'
                },
                series: [{
                    name: 'Revenue',
                    data: @json($revenueByMonth)
                }],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '45%',
                        borderRadius: 6
                    }
                },
                colors: ['#2563eb'],
                dataLabels: { enabled: false },
                stroke: { show: true, width: 2, colors: ['transparent'] },
                xaxis: {
                    categories: @json($monthLabels),
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: {
                        style: {
                            colors: '#9ca3af',
                            fontSize: '12px',
                            fontFamily: 'Inter, ui-sans-serif',
                            fontWeight: 400
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#9ca3af',
                            fontSize: '12px',
                            fontFamily: 'Inter, ui-sans-serif',
                            fontWeight: 400
                        },
                        formatter: (value) => value >= 1000 ? (value / 1000).toFixed(0) + 'k' : value
                    }
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 3
                },
                states: {
                    hover: { filter: { type: 'darken', value: 0.9 } }
                },
                tooltip: {
                    y: { formatter: (value) => 'KES ' + value.toLocaleString() }
                }
            }).render();
        }

        // ============ TICKETS AREA CHART ============
        const ticketsEl = document.querySelector('#chart-tickets');
        if (ticketsEl) {
            new ApexCharts(ticketsEl, {
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    fontFamily: 'Inter, ui-sans-serif'
                },
                series: [{
                    name: 'Tickets',
                    data: @json($ticketsByMonth)
                }],
                colors: ['#6366f1'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        type: 'vertical',
                        shadeIntensity: 1,
                        opacityFrom: 0.35,
                        opacityTo: 0.02,
                        stops: [0, 90, 100]
                    }
                },
                xaxis: {
                    categories: @json($monthLabels),
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    crosshairs: {
                        stroke: { dashArray: 0 },
                        dropShadow: { show: false }
                    },
                    labels: {
                        style: {
                            colors: '#9ca3af',
                            fontSize: '12px',
                            fontFamily: 'Inter, ui-sans-serif',
                            fontWeight: 400
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#9ca3af',
                            fontSize: '12px',
                            fontFamily: 'Inter, ui-sans-serif',
                            fontWeight: 400
                        },
                        formatter: (value) => value >= 1000 ? (value / 1000).toFixed(0) + 'k' : value
                    }
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 3
                },
                tooltip: {
                    y: { formatter: (value) => value + ' tickets' }
                }
            }).render();
        }
    });
</script>
@endpush