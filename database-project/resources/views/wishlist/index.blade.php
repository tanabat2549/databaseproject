<x-layouts::app :title="__('My Wishlist')">
    <div class="flex w-full flex-col gap-6">
        <div>
            <flux:heading size="xl" level="1">{{ __('My Wishlist') }}</flux:heading>
            <flux:subheading>{{ __('Flowers you saved for later') }}</flux:subheading>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-700 dark:border-green-800 dark:bg-green-950 dark:text-green-300">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse ($products as $product)
                <div class="flex flex-col overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <a href="{{ route('products.show', $product) }}">
                        <img src="{{ $product->image }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover bg-zinc-100 dark:bg-zinc-800">
                    </a>

                    <div class="flex flex-1 flex-col gap-2 p-4">
                        <a href="{{ route('products.show', $product) }}" class="font-semibold hover:underline">{{ $product->name }}</a>

                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium">฿{{ number_format($product->price, 2) }}</span>
                            <span class="text-zinc-500">
                                ★ {{ number_format($product->reviews_avg_rating ?? 0, 1) }} ({{ $product->reviews_count }})
                            </span>
                        </div>

                        @if ($product->stock > 0)
                            <span class="text-xs text-green-600 dark:text-green-400">{{ __('In stock: :n', ['n' => $product->stock]) }}</span>
                        @else
                            <span class="text-xs text-red-600 dark:text-red-400">{{ __('Out of stock') }}</span>
                        @endif

                        <div class="mt-auto flex gap-2 pt-2">
                            <form method="POST" action="{{ route('wishlist.move', $product) }}" class="flex-1">
                                @csrf
                                <flux:button type="submit" variant="primary" size="sm" class="w-full" icon="shopping-cart"
                                             :disabled="$product->stock < 1" data-test="move-to-cart-button">
                                    {{ __('Move to cart') }}
                                </flux:button>
                            </form>

                            <form method="POST" action="{{ route('wishlist.destroy', $product) }}">
                                @csrf
                                @method('DELETE')
                                <flux:button type="submit" variant="danger" size="sm" icon="trash" data-test="remove-wishlist-button">
                                    <span class="sr-only">{{ __('Remove') }}</span>
                                </flux:button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
                    <flux:text>{{ __('Your wishlist is empty.') }}</flux:text>
                    <flux:button :href="route('products.index')" variant="primary" class="mt-4">
                        {{ __('Browse flowers') }}
                    </flux:button>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts::app>
