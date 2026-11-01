{{--
    <x-button>Label</x-button>                          primary (black pill)
    <x-button variant="outline" :href="route('...')">   outline link
    variants: primary | outline | light
--}}
@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
    'size' => 'md',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-full font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-gulu-lilac-deep focus-visible:ring-offset-2 disabled:opacity-50';
    $variants = [
        'primary' => 'bg-gulu-ink text-white hover:bg-gulu-plum',
        'outline' => 'border border-gulu-ink text-gulu-ink hover:bg-gulu-ink hover:text-white',
        'light' => 'bg-white text-gulu-ink hover:bg-gulu-lilac-soft',
    ];
    $sizes = [
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-7 py-3.5 text-[15px]',
        'lg' => 'px-9 py-4.5 text-base',
    ];
    $classes = $base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
