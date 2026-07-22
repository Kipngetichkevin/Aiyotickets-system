{{-- partials/card/metric-card.blade.php --}}
@props([
    'title' => '',
    'value' => '0',
    'prefix' => '',
    'suffix' => '',
    'icon' => null,
    'color' => 'blue'
])

<div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
    <div class="p-4 md:p-5">
        <div class="flex items-center gap-x-2">
            @if($icon)
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-{{ $color }}-100 dark:bg-{{ $color }}-900/20">
                    {!! $icon !!}
                </div>
            @endif
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
                {{ $title }}
            </p>
        </div>

        <div class="mt-1 flex items-center gap-x-2">
            <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                {{ $prefix }} {{ number_format($value, 2) }} {{ $suffix }}
            </h3>
        </div>
    </div>
</div>