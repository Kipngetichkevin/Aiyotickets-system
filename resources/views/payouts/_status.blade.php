@php
    $styles = [
        'requested' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
        'approved'  => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
        'paid'      => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
        'rejected'  => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
    ];
@endphp
<span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize {{ $styles[$status] ?? 'bg-gray-100 text-gray-700' }}">{{ $status }}</span>
