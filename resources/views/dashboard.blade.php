@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Metrics Grid -->
    <div class="grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <!-- Current Events -->
                <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
                    <div class="p-4 md:p-5">
                        <div class="flex items-center gap-x-2">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
                                Current Events
                            </p>
                        </div>
                        <div class="mt-1 flex items-center gap-x-2">
                            <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                                {{ $currentEventsCount ?? 0 }}
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Tickets Sold -->
                <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
                    <div class="p-4 md:p-5">
                        <div class="flex items-center gap-x-2">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
                                Tickets Sold
                            </p>
                        </div>
                        <div class="mt-1 flex items-center gap-x-2">
                            <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                                {{ $ticketsSold ?? 0 }}
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Total Sales -->
                <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
                    <div class="p-4 md:p-5">
                        <div class="flex items-center gap-x-2">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
                                Total Sales
                            </p>
                        </div>
                        <div class="mt-1 flex items-center gap-x-2">
                            <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                                KES {{ number_format($totalSales ?? 0, 2) }}
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Net Sales -->
                <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
                    <div class="p-4 md:p-5">
                        <div class="flex items-center gap-x-2">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
                                Net Sales
                            </p>
                        </div>
                        <div class="mt-1 flex items-center gap-x-2">
                            <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                                KES {{ number_format($netSales ?? 0, 2) }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-12 gap-4 md:gap-6 mt-6">
        <div class="col-span-12 lg:col-span-6">
            @include('partials.chart.income-chart')
        </div>
        <div class="col-span-12 lg:col-span-6">
            @include('partials.chart.tickets-chart', ['payments' => $payments ?? collect()])
        </div>
    </div>

    <!-- Include the events table partial -->
    @include('partials.table.table-events', ['events' => $events ?? []])
    

    <!-- Recent Attendees Section -->
    <div class="grid grid-cols-12 gap-4 md:gap-6 mt-6">
        <div class="col-span-12">
            @include('partials.card.attendees-card', ['attendees' => $attendees ?? collect()])
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Dashboard specific styles */
    .bg-brand-500 {
        background-color: #3b82f6;
    }
    .bg-brand-500:hover {
        background-color: #2563eb;
    }
    
    /* Chart container styles */
    #hs-multiple-bar-charts,
    #hs-single-area-chart {
        height: 300px;
        width: 100%;
    }
</style>
@endpush

@push('scripts')
    <!-- JS Implementing Plugins -->
    <script src="https://cdn.jsdelivr.net/npm/preline/dist/preline.min.js"></script>
    
    <!-- Apexcharts -->
    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://preline.co/assets/js/hs-apexcharts-helpers.js"></script>
    
    <!-- Chart Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Income Bar Chart
            (function () {
                buildChart(
                    "#hs-multiple-bar-charts",
                    (mode) => ({
                        chart: {
                            type: "bar",
                            height: 300,
                            toolbar: { show: false },
                            zoom: { enabled: false },
                        },
                        series: [
                            {
                                name: "Chosen Period",
                                data: [23000, 44000, 55000, 57000, 56000, 61000, 58000, 63000, 60000, 66000, 34000, 78000],
                            },
                            {
                                name: "Last Period",
                                data: [17000, 76000, 85000, 101000, 98000, 87000, 105000, 91000, 114000, 94000, 67000, 66000],
                            },
                        ],
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: "16px",
                                borderRadius: 0,
                            },
                        },
                        legend: { show: false },
                        dataLabels: { enabled: false },
                        stroke: {
                            show: true,
                            width: 8,
                            colors: ["transparent"],
                        },
                        xaxis: {
                            categories: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            crosshairs: { show: false },
                            labels: {
                                style: {
                                    colors: "#9ca3af",
                                    fontSize: "13px",
                                    fontFamily: "Inter, ui-sans-serif",
                                    fontWeight: 400,
                                },
                                offsetX: -2,
                                formatter: (title) => title.slice(0, 3),
                            },
                        },
                        yaxis: {
                            labels: {
                                align: "left",
                                minWidth: 0,
                                maxWidth: 140,
                                style: {
                                    colors: "#9ca3af",
                                    fontSize: "13px",
                                    fontFamily: "Inter, ui-sans-serif",
                                    fontWeight: 400,
                                },
                                formatter: (value) => (value >= 1000 ? `${value / 1000}k` : value),
                            },
                        },
                        states: {
                            hover: {
                                filter: {
                                    type: "darken",
                                    value: 0.9,
                                },
                            },
                        },
                        tooltip: {
                            y: {
                                formatter: (value) => `$${value >= 1000 ? `${value / 1000}k` : value}`,
                            },
                            custom: function (props) {
                                const { categories } = props.ctx.opts.xaxis;
                                const { dataPointIndex } = props;
                                const title = categories[dataPointIndex];
                                const newTitle = `${title}`;

                                return buildTooltip(props, {
                                    title: newTitle,
                                    mode,
                                    hasTextLabel: true,
                                    wrapperExtClasses: "min-w-28",
                                    labelDivider: ":",
                                    labelExtClasses: "ms-2",
                                });
                            },
                        },
                        responsive: [
                            {
                                breakpoint: 568,
                                options: {
                                    chart: { height: 300 },
                                    plotOptions: {
                                        bar: { columnWidth: "14px" },
                                    },
                                    stroke: { width: 8 },
                                    labels: {
                                        style: {
                                            colors: "#9ca3af",
                                            fontSize: "11px",
                                            fontFamily: "Inter, ui-sans-serif",
                                            fontWeight: 400,
                                        },
                                        offsetX: -2,
                                        formatter: (title) => title.slice(0, 3),
                                    },
                                    yaxis: {
                                        labels: {
                                            align: "left",
                                            minWidth: 0,
                                            maxWidth: 140,
                                            style: {
                                                colors: "#9ca3af",
                                                fontSize: "11px",
                                                fontFamily: "Inter, ui-sans-serif",
                                                fontWeight: 400,
                                            },
                                            formatter: (value) => value >= 1000 ? `${value / 1000}k` : value,
                                        },
                                    },
                                },
                            },
                        ],
                    }),
                    {
                        colors: ["#2563eb", "#d1d5db"],
                        grid: { borderColor: "#e5e7eb" },
                    },
                    {
                        colors: ["#6b7280", "#2563eb"],
                        grid: { borderColor: "#404040" },
                    }
                );
            })();

            // Tickets Area Chart
            (function () {
                const payments = @json($payments ?? []);
                const paymentData = payments.map(p => p.total_price || 0);
                const paymentDates = payments.map(p => p.created_at ? new Date(p.created_at).toLocaleDateString('en-US', { day: '2-digit', month: 'short' }) : '');

                buildChart(
                    "#hs-single-area-chart",
                    (mode) => ({
                        chart: {
                            height: 300,
                            type: "area",
                            toolbar: { show: false },
                            zoom: { enabled: false },
                        },
                        series: [
                            {
                                name: "Payments",
                                data: paymentData.length > 0 ? paymentData : [0, 0, 0, 0, 0],
                            },
                        ],
                        legend: { show: false },
                        dataLabels: { enabled: false },
                        stroke: {
                            curve: "straight",
                            width: 2,
                        },
                        grid: {
                            strokeDashArray: 2,
                        },
                        fill: {
                            type: "gradient",
                            gradient: {
                                type: "vertical",
                                shadeIntensity: 1,
                                opacityFrom: 0.1,
                                opacityTo: 0.8,
                            },
                        },
                        xaxis: {
                            type: "category",
                            tickPlacement: "on",
                            categories: paymentDates.length > 0 ? paymentDates : ["Jan", "Feb", "Mar", "Apr", "May"],
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            crosshairs: {
                                stroke: { dashArray: 0 },
                                dropShadow: { show: false },
                            },
                            tooltip: { enabled: false },
                            labels: {
                                style: {
                                    colors: "#9ca3af",
                                    fontSize: "13px",
                                    fontFamily: "Inter, ui-sans-serif",
                                    fontWeight: 400,
                                },
                                formatter: (title) => {
                                    let t = title;
                                    if (t && t.includes(' ')) {
                                        const newT = t.split(" ");
                                        t = `${newT[0]} ${newT[1]?.slice(0, 3) || ''}`;
                                    }
                                    return t;
                                },
                            },
                        },
                        yaxis: {
                            labels: {
                                align: "left",
                                minWidth: 0,
                                maxWidth: 140,
                                style: {
                                    colors: "#9ca3af",
                                    fontSize: "13px",
                                    fontFamily: "Inter, ui-sans-serif",
                                    fontWeight: 400,
                                },
                                formatter: (value) => (value >= 1000 ? `${value / 1000}k` : value),
                            },
                        },
                        tooltip: {
                            x: {
                                format: "MMMM yyyy",
                            },
                            y: {
                                formatter: (value) => `${value >= 1000 ? `${value / 1000}k` : value}`,
                            },
                            custom: function (props) {
                                const { categories } = props.ctx.opts.xaxis;
                                const { dataPointIndex } = props;
                                const title = categories[dataPointIndex] || '';
                                const newTitle = title.includes(' ') ? title.split(" ").join(" ") : title;

                                return buildTooltip(props, {
                                    title: newTitle,
                                    mode,
                                    valuePrefix: "",
                                    hasTextLabel: true,
                                    markerExtClasses: "!rounded-sm",
                                    wrapperExtClasses: "min-w-28",
                                });
                            },
                        },
                        responsive: [
                            {
                                breakpoint: 568,
                                options: {
                                    chart: { height: 300 },
                                    labels: {
                                        style: {
                                            colors: "#9ca3af",
                                            fontSize: "11px",
                                            fontFamily: "Inter, ui-sans-serif",
                                            fontWeight: 400,
                                        },
                                        offsetX: -2,
                                        formatter: (title) => title.slice(0, 3),
                                    },
                                    yaxis: {
                                        labels: {
                                            align: "left",
                                            minWidth: 0,
                                            maxWidth: 140,
                                            style: {
                                                colors: "#9ca3af",
                                                fontSize: "11px",
                                                fontFamily: "Inter, ui-sans-serif",
                                                fontWeight: 400,
                                            },
                                            formatter: (value) => value >= 1000 ? `${value / 1000}k` : value,
                                        },
                                    },
                                },
                            },
                        ],
                    }),
                    {
                        colors: ["#2563eb", "#9333ea"],
                        fill: {
                            gradient: {
                                stops: [0, 90, 100],
                            },
                        },
                        grid: { borderColor: "#e5e7eb" },
                    },
                    {
                        colors: ["#3b82f6", "#a855f7"],
                        fill: {
                            gradient: {
                                stops: [100, 90, 0],
                            },
                        },
                        grid: { borderColor: "#404040" },
                    }
                );
            })();
        });
    </script>
    @vite('resources/js/app.js')
@endpush