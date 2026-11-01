{{--
    Clickable 1-5 score for the review form (posts "rating"):
    <x-rating-input name="rating" :value="old('rating', $review->rating ?? 5)" />
--}}
@props([
    'name' => 'rating',
    'value' => null,
])

<div {{ $attributes->class('flex gap-3') }} role="radiogroup" aria-label="{{ __('Rating') }}">
    @for ($i = 1; $i <= 5; $i++)
        <label class="cursor-pointer">
            <input type="radio" name="{{ $name }}" value="{{ $i }}" class="peer sr-only" @checked((int) $value === $i) required>
            <span class="flex size-14 items-center justify-center rounded-full border border-gulu-line text-xl font-medium transition peer-checked:border-gulu-ink peer-checked:bg-gulu-ink peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-gulu-lilac-deep">
                {{ $i }}
            </span>
        </label>
    @endfor
</div>
