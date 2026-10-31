{{--
    Figma: 07 Cart. Controller: CartController@index
    $cartItems (CartItem + product), $totalAmount (bcmath string "1780.00"), $hasStockIssues (bool)
    Row actions: PATCH cart.update (action=increment|decrement), DELETE cart.destroy.
--}}
<x-layouts::shop :title="__('Your cart')">
    <div class="flex flex-col gap-10">
        <div class="flex flex-col gap-5">
            <div>
                <x-button variant="outline" size="sm" :href="route('products.index')">← {{ __('Back') }}</x-button>
            </div>

            <div>
                <h1 class="font-display text-6xl font-medium tracking-tight lg:text-7xl">{{ __('Your cart') }}</h1>
                <p class="mt-2 text-gulu-muted">{{ trans_choice('{0} Nothing here yet|{1} 1 item|[2,*] :count items', $cartItems->count()) }}</p>
            </div>
        </div>

        @if ($cartItems->isEmpty())
            <div class="flex flex-col items-center gap-5 rounded-3xl bg-gulu-mist px-6 py-20 text-center">
                <flux:icon.shopping-bag class="size-10 text-gulu-lilac-deep" />
                <p class="font-display text-4xl">{{ __('Your cart is empty.') }}</p>
                <p class="text-gulu-muted">{{ __('Add a bouquet and it will show up here.') }}</p>
                <x-button :href="route('products.index')">{{ __('Browse flowers') }}</x-button>
            </div>
        @else
            @if ($hasStockIssues)
                <div class="rounded-2xl bg-gulu-gold/20 px-5 py-3 text-sm" role="alert">
                    {{ __('Some items are out of stock or exceed the stock we have. Please adjust them before ordering.') }}
                </div>
            @endif

            <div class="grid items-start gap-10 lg:grid-cols-[1fr_400px]">
                <ul class="flex flex-col gap-4" data-test="cart-items">
                    @foreach ($cartItems as $item)
                        @php($product = $item->product)
                        <li class="flex flex-col gap-5 rounded-3xl bg-gulu-mist p-5 sm:flex-row sm:items-center" data-test="cart-item">
                            <div class="h-36 w-32 shrink-0 overflow-hidden rounded-2xl bg-gulu-lilac-soft">
                                @if ($product)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="size-full object-cover">
                                @endif
                            </div>

                            <div class="flex flex-1 flex-col gap-1.5">
                                <p class="text-xl font-medium">{{ $product?->name ?? __('Unavailable product') }}</p>
                                @if ($product)
                                    <p class="text-sm uppercase tracking-widest text-gulu-muted">{{ $product->category }}</p>
                                    <p class="text-sm text-gulu-muted">฿{{ number_format((float) $product->price, 2) }} × {{ $item->quantity }}</p>
                                @endif
                                @if ($item->has_stock_issue)
                                    <p class="text-sm font-medium text-red-600">
                                        {{ $product ? __('Only :n left in stock', ['n' => $product->stock]) : __('This product is no longer available') }}
                                    </p>
                                @endif

                                <div class="mt-3 flex flex-wrap items-center gap-3">
                                    <div class="inline-flex items-center gap-4 rounded-full border border-gulu-line bg-white px-4 py-2">
                                        <form method="POST" action="{{ route('cart.update', $item) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="action" value="decrement">
                                            <button type="submit" class="text-xl leading-none" aria-label="{{ __('Decrease') }}">−</button>
                                        </form>
                                        <span class="w-6 text-center font-medium" data-test="cart-quantity">{{ $item->quantity }}</span>
                                        <form method="POST" action="{{ route('cart.update', $item) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="action" value="increment">
                                            <button type="submit" class="text-xl leading-none" aria-label="{{ __('Increase') }}">+</button>
                                        </form>
                                    </div>

                                    <form method="POST" action="{{ route('cart.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" variant="outline" size="sm" data-test="cart-delete-button">
                                            <flux:icon.trash class="size-4" /> {{ __('delete') }}
                                        </x-button>
                                    </form>
                                </div>
                            </div>

                            <p class="text-xl font-semibold sm:text-right">฿{{ number_format((float) $item->subtotal_decimal, 2) }}</p>
                        </li>
                    @endforeach
                </ul>

                <aside class="flex flex-col gap-5 rounded-3xl bg-gulu-lilac-soft p-7 lg:sticky lg:top-32">
                    <h2 class="font-display text-4xl font-medium">{{ __('Order summary') }}</h2>

                    <dl class="flex flex-col gap-3 text-base">
                        <div class="flex justify-between">
                            <dt class="text-gulu-muted">{{ __('Subtotal') }}</dt>
                            <dd>฿{{ number_format((float) $totalAmount, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gulu-muted">{{ __('Delivery') }}</dt>
                            <dd>{{ __('Free') }}</dd>
                        </div>
                    </dl>

                    <hr class="border-gulu-line">

                    <div class="flex items-baseline justify-between">
                        <span class="font-medium">{{ __('total price') }}</span>
                        <span class="text-3xl font-semibold" data-test="cart-total">฿{{ number_format((float) $totalAmount, 2) }}</span>
                    </div>

                    @if ($hasStockIssues)
                        <x-button type="button" class="w-full" disabled>{{ __('order') }}</x-button>
                    @else
                        <x-button :href="route('checkout.index')" class="w-full" data-test="go-to-checkout">{{ __('order') }}</x-button>
                    @endif
                </aside>
            </div>
        @endif
    </div>
</x-layouts::shop>
