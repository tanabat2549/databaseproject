{{--
    Read-only stars: <x-rating-stars :rating="$product->averageRating()" :count="$product->reviewsCount()" />
    size = pixel size of one star (default 16). Omit :count to hide the number.
--}}
@props([
    'rating' => 0,
    'count' => null,
    'size' => 16,
])

@php($filled = (int) round((float) $rating))

<div {{ $attributes->class('inline-flex items-center gap-2') }} aria-label="{{ __('Rating :n out of 5', ['n' => number_format((float) $rating, 1)]) }}">
    <span class="inline-flex gap-0.5">
        @for ($i = 1; $i <= 5; $i++)
            <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" aria-hidden="true"
                 class="{{ $i <= $filled ? 'fill-gulu-gold stroke-gulu-gold' : 'fill-none stroke-gulu-line' }}"
                 stroke-width="1.5" stroke-linejoin="round">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26" />
            </svg>
        @endfor
    </span>

    @if (! is_null($count))
        <span class="text-sm text-gulu-muted">{{ number_format((float) $rating, 1) }} · {{ $count }} {{ __('reviews') }}</span>
    @endif
</div>
