{{-- Figma: 06 Wishlist. Controller: WishlistController@index ($products with reviews_avg_rating / reviews_count) --}}
<x-layouts::shop :title="__('My Wishlist')">
    <div class="flex flex-col gap-10">
        <div class="flex flex-col gap-5">
            <div>
                <x-button variant="outline" size="sm" :href="route('products.index')">← {{ __('Back') }}</x-button>
            </div>

            <div>
                <h1 class="font-display text-6xl font-medium tracking-tight lg:text-7xl">{{ __('Wishlist') }}</h1>
                <p class="mt-2 text-gulu-muted">{{ trans_choice('{0} No saved flowers yet|{1} 1 saved flower|[2,*] :count saved flowers', $products->count()) }}</p>
            </div>
        </div>

        @if ($products->isEmpty())
            <div class="flex flex-col items-center gap-5 rounded-3xl bg-gulu-mist px-6 py-20 text-center">
                <flux:icon.heart class="size-10 text-gulu-lilac-deep" />
                <p class="font-display text-4xl">{{ __('Your wishlist is empty.') }}</p>
                <p class="text-gulu-muted">{{ __('Tap the heart on any flower to save it for later.') }}</p>
                <x-button :href="route('products.index')">{{ __('Browse flowers') }}</x-button>
            </div>
        @else
            <div class="grid gap-x-5 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    <div class="flex flex-col gap-4" data-test="wishlist-item">
                        <x-product-card :product="$product" />

                        <p @class(['text-sm', 'text-gulu-success' => $product->stock > 0, 'text-red-600' => $product->stock < 1])>
                            {{ $product->stock > 0 ? __('In stock: :n', ['n' => $product->stock]) : __('Out of stock') }}
                        </p>

                        <div class="mt-auto flex gap-2.5">
                            <form method="POST" action="{{ route('wishlist.move', $product) }}" class="flex-1">
                                @csrf
                                <x-button type="submit" size="sm" class="w-full" :disabled="$product->stock < 1" data-test="move-to-cart-button">
                                    <flux:icon.shopping-bag class="size-4" /> {{ __('Move to cart') }}
                                </x-button>
                            </form>

                            <form method="POST" action="{{ route('wishlist.destroy', $product) }}">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="outline" size="sm" data-test="remove-wishlist-button" aria-label="{{ __('Remove') }}">
                                    <flux:icon.trash class="size-4" /> {{ __('Remove') }}
                                </x-button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts::shop>
