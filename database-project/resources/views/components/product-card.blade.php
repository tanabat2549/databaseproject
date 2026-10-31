{{--
    Catalog card. Pass a Product; eager-load withAvg('reviews','rating') + withCount('reviews') for the stars.
    <x-product-card :product="$product" />
--}}
@props(['product'])

<article {{ $attributes->class('group flex flex-col gap-3.5') }}>
    <a href="{{ route('products.show', $product) }}" class="relative block overflow-hidden rounded-2xl bg-gulu-lilac-soft">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
             class="aspect-[4/5] w-full object-cover transition duration-500 group-hover:scale-105">

        @if ($product->stock < 1)
            <span class="absolute left-3 top-3 rounded-full bg-white px-3 py-1 text-xs font-medium">{{ __('Sold out') }}</span>
        @endif
    </a>

    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <a href="{{ route('products.show', $product) }}" class="block truncate text-[17px] font-medium hover:underline">{{ $product->name }}</a>
            <p class="text-sm uppercase tracking-widest text-gulu-muted">{{ $product->category }}</p>
        </div>
        <p class="shrink-0 text-[17px] font-semibold">฿{{ number_format((float) $product->price) }}</p>
    </div>

    @if (isset($product->reviews_avg_rating))
        <x-rating-stars :rating="$product->reviews_avg_rating" :count="$product->reviews_count" :size="14" />
    @endif
</article>
