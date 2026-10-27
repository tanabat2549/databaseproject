{{-- Heart toggle for catalog / product detail pages: <x-wishlist-button :product="$product" /> --}}
@props(['product'])

<div {{ $attributes }}>
    @auth
        @php($inWishlist = auth()->user()->wishlist()->whereKey($product->id)->exists())

        <form method="POST" action="{{ route($inWishlist ? 'wishlist.destroy' : 'wishlist.store', $product) }}">
            @csrf
            @if ($inWishlist)
                @method('DELETE')
            @endif
            <flux:button type="submit" size="sm" variant="ghost" icon="heart"
                         class="{{ $inWishlist ? 'text-pink-600 dark:text-pink-400' : '' }}"
                         data-test="wishlist-toggle-button">
                {{ $inWishlist ? __('Saved') : __('Add to wishlist') }}
            </flux:button>
        </form>
    @else
        <flux:button :href="route('login')" size="sm" variant="ghost" icon="heart">
            {{ __('Add to wishlist') }}
        </flux:button>
    @endauth
</div>
