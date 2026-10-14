@php($label = $bill->statusLabel())
<span class="whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ match ($label) {
    'Paid'              => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
    'Overdue'           => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
    'Awaiting approval' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
    'Part paid'         => 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400',
    'Void'              => 'bg-gray-100 text-gray-500 line-through dark:bg-gray-800',
    default             => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
} }}">{{ $label }}</span>
