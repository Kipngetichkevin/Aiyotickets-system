{{-- resources/views/reports/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<main>
    <div x-data="{ pageName: 'Reports' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">

        {{-- HEADER + FILTERS --}}
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Reports</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Paid tickets and bookings only.</p>
            </div>

            <form method="GET" action="{{ route('reports') }}" class="flex flex-wrap items-end gap-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Event</label>
                    <select name="event_id" class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All events &amp; experiences</option>
                        @foreach ($events as $e)
                            <option value="{{ $e->id }}" @selected(($filters['event_id'] ?? null) == $e->id)>{{ $e->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">From</label>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">To</label>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <button type="submit" class="h-10 rounded-lg bg-brand-500 px-4 text-sm font-semibold text-white hover:bg-brand-600">Apply</button>
                @if (array_filter($filters))
                    <a href="{{ route('reports') }}" class="h-10 inline-flex items-center rounded-lg border border-gray-200 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200">Reset</a>
                @endif
            </form>
        </div>

        @error('event_id')<p class="mb-4 text-sm text-red-600">{{ $message }}</p>@enderror

        {{-- KPIs --}}
        <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-6">
            @foreach ([
                ['Gross sales', 'KES ' . number_format($totals['gross']), 'Before platform fee', 'text-gray-900 dark:text-white'],
                ['Platform fee', 'KES ' . number_format($totals['fee']), $feePercent . '% of gross', 'text-gray-900 dark:text-white'],
                ['Net earnings', 'KES ' . number_format($totals['net']), 'Yours after fee', 'text-brand-600'],
                ['Tickets sold', number_format($totals['tickets']), 'Event tickets', 'text-gray-900 dark:text-white'],
                ['Bookings', number_format($totals['bookings']), 'Experience places', 'text-gray-900 dark:text-white'],
                ['Attendance', $totals['attendance'] . '%', number_format($totals['checked_in']) . ' checked in', 'text-green-600'],
            ] as [$label, $value, $hint, $color])
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</p>
                    <p class="mt-2 text-2xl font-bold {{ $color }}">{{ $value }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
                </div>
            @endforeach
        </div>

        {{-- DAILY SALES --}}
        <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-4 text-sm font-bold text-gray-800 dark:text-white/90">Daily sales (KES)</h3>
            @if ($daily->isEmpty())
                <p class="py-10 text-center text-sm text-gray-500 dark:text-gray-400">No paid sales in this period.</p>
            @else
                <div id="dailySalesChart" class="-ml-2"></div>
            @endif
        </div>

        {{-- BY EVENT --}}
        <div class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white/90">Events</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3">Event</th>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3 text-right">Sold / capacity</th>
                            <th class="px-5 py-3 text-right">Checked in</th>
                            <th class="px-5 py-3 text-right">Gross (KES)</th>
                            <th class="px-5 py-3 text-right">Attendees</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($byEvent as $row)
                            <tr class="text-gray-700 dark:text-gray-300">
                                <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $row->name }}</td>
                                <td class="px-5 py-3">{{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d M Y') : '—' }}</td>
                                <td class="px-5 py-3 text-right">{{ number_format($row->sold) }} / {{ number_format($row->capacity) }}</td>
                                <td class="px-5 py-3 text-right">{{ number_format($row->checked_in) }}</td>
                                <td class="px-5 py-3 text-right font-semibold">{{ number_format($row->gross) }}</td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('reports.attendees', $row->id) }}" class="text-xs font-semibold text-brand-600 hover:underline">Download CSV</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No ticket sales yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            {{-- BY TICKET TYPE --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white/90">Ticket types</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3">Ticket</th>
                                <th class="px-5 py-3 text-right">Price</th>
                                <th class="px-5 py-3 text-right">Sold</th>
                                <th class="px-5 py-3 text-right">Gross (KES)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($byTicketType as $row)
                                <tr class="text-gray-700 dark:text-gray-300">
                                    <td class="px-5 py-3">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $row->name }}</span>
                                        <span class="block text-xs text-gray-500">{{ $row->event_name }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right">{{ number_format($row->price) }}</td>
                                    <td class="px-5 py-3 text-right">{{ number_format($row->sold) }} / {{ number_format($row->quantity) }}</td>
                                    <td class="px-5 py-3 text-right font-semibold">{{ number_format($row->gross) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No ticket sales yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- BY EXPERIENCE --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white/90">Experiences</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3">Experience</th>
                                <th class="px-5 py-3 text-right">Bookings</th>
                                <th class="px-5 py-3 text-right">Checked in</th>
                                <th class="px-5 py-3 text-right">Gross (KES)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($byExperience as $row)
                                <tr class="text-gray-700 dark:text-gray-300">
                                    <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $row->name }}</td>
                                    <td class="px-5 py-3 text-right">{{ number_format($row->bookings) }}</td>
                                    <td class="px-5 py-3 text-right">{{ number_format($row->checked_in) }}</td>
                                    <td class="px-5 py-3 text-right font-semibold">{{ number_format($row->gross) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No experience bookings{{ !empty($filters['event_id']) ? ' (event filter applied)' : '' }}.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@if ($daily->isNotEmpty())
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.1"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const daily = @json($daily);
    new ApexCharts(document.querySelector('#dailySalesChart'), {
        chart: { type: 'area', height: 280, fontFamily: 'Outfit, sans-serif', toolbar: { show: false } },
        series: [{ name: 'Sales (KES)', data: Object.values(daily) }],
        xaxis: { categories: Object.keys(daily), labels: { rotate: 0, hideOverlappingLabels: true } },
        colors: ['#465FFF'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.45, opacityTo: 0 } },
        yaxis: { labels: { formatter: v => Math.round(v).toLocaleString() } },
        grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
    }).render();
});
</script>
@endpush
@endif
