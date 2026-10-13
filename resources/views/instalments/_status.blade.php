@php
    $labels = ['partial' => 'Paying', 'paid' => 'Fully paid', 'lapsed' => 'Lapsed'];
    $styles = [
        'partial' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
        'paid'    => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
        'lapsed'  => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
    ];
@endphp
<span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $styles[$status] ?? 'bg-gray-100 text-gray-700' }}">{{ $labels[$status] ?? ucfirst($status) }}</span>
