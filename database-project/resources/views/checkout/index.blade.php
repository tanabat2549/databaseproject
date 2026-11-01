<x-layouts::shop :title="__('Checkout')">
    <div class="flex flex-col gap-6">
        <div>
            <x-button variant="outline" size="sm" :href="route('cart.index')" class="w-fit">
                <flux:icon.arrow-left class="size-4" />{{ __('Back') }}
            </x-button>
        </div>

        <div>
            <h1 class="font-display text-4xl sm:text-5xl font-medium tracking-tight text-gulu-ink">{{ __('Checkout') }}</h1>
            <p class="mt-2 text-base text-gulu-muted">{{ __('Review your items and confirm the order') }}</p>
        </div>

        <form method="POST" action="{{ route('checkout.store') }}" class="mt-4">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                {{-- Left: Shipping Form & Items list --}}
                <div class="lg:col-span-7 flex flex-col gap-8">
                    {{-- Shipping Details Card --}}
                    <section class="rounded-3xl border border-gulu-line bg-white p-6 sm:p-8 flex flex-col gap-6" aria-labelledby="shipping-heading">
                        <h2 id="shipping-heading" class="font-display text-3xl text-gulu-ink">{{ __('Shipping') }}</h2>

                        {{-- Shipping Address --}}
                        <div class="flex flex-col gap-2">
                            <label for="shipping_address" class="text-[13px] font-medium uppercase tracking-wider text-gulu-muted">
                                {{ __('Shipping Address') }}
                            </label>
                            <textarea id="shipping_address" name="shipping_address" rows="3" required
                                placeholder="123 Flower Road, Chiang Mai 50200"
                                class="w-full rounded-2xl border bg-white px-5 py-4 text-base outline-none transition placeholder:text-gulu-muted focus:border-gulu-ink focus:ring-2 focus:ring-gulu-lilac/40 {{ $errors->has('shipping_address') ? 'border-red-500' : 'border-gulu-line' }}">{{ old('shipping_address', '123 Flower Road, Chiang Mai 50200') }}</textarea>
                            @error('shipping_address')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Delivery Date --}}
                        <div class="flex flex-col gap-2">
                            <label for="delivery_date" class="text-[13px] font-medium uppercase tracking-wider text-gulu-muted">
                                {{ __('Delivery Date') }}
                            </label>
                            <input type="date" id="delivery_date" name="delivery_date" required
                                min="{{ date('Y-m-d') }}"
                                value="{{ old('delivery_date', now()->addDays(2)->format('Y-m-d')) }}"
                                class="w-full rounded-2xl border bg-white px-5 py-4 text-base outline-none transition placeholder:text-gulu-muted focus:border-gulu-ink focus:ring-2 focus:ring-gulu-lilac/40 {{ $errors->has('delivery_date') ? 'border-red-500' : 'border-gulu-line' }}">
                            @error('delivery_date')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Phone Selection (from user_phones) --}}
                        <div class="flex flex-col gap-2">
                            <label for="phone_no" class="text-[13px] font-medium uppercase tracking-wider text-gulu-muted">
                                {{ __('Phone') }}
                            </label>
                            @if ($phones->isNotEmpty())
                                <select id="phone_no" name="phone_no"
                                    class="w-full rounded-2xl border border-gulu-line bg-white px-5 py-4 text-base outline-none transition focus:border-gulu-ink focus:ring-2 focus:ring-gulu-lilac/40">
                                    @foreach ($phones as $phone)
                                        <option value="{{ $phone }}" @selected(old('phone_no') === $phone)>
                                            {{ $phone }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="tel" id="phone_no" name="phone_no" placeholder="0811111111" value="{{ old('phone_no') }}"
                                    class="w-full rounded-2xl border border-gulu-line bg-white px-5 py-4 text-base outline-none transition placeholder:text-gulu-muted focus:border-gulu-ink focus:ring-2 focus:ring-gulu-lilac/40">
                                <a href="{{ route('profile.show') }}" class="text-xs text-gulu-muted hover:text-gulu-ink underline">
                                    {{ __('ยังไม่มีเบอร์โทรศัพท์ที่บันทึกไว้ คลิกเพื่อเพิ่มเบอร์ในหน้า Profile') }}
                                </a>
                            @endif
                            @error('phone_no')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </section>

                    {{-- Items Section --}}
                    <section class="flex flex-col gap-4" aria-labelledby="items-heading">
                        <h2 id="items-heading" class="font-display text-3xl text-gulu-ink">{{ __('Items') }}</h2>

                        <div class="flex flex-col gap-3">
                            @foreach ($cartItems as $item)
                                <article class="flex items-center justify-between gap-4 rounded-2xl bg-gulu-mist p-4 sm:p-5">
                                    <div class="flex items-center gap-4 min-w-0">
                                        <div class="size-16 sm:size-20 shrink-0 overflow-hidden rounded-2xl bg-gulu-lilac-soft flex items-center justify-center">
                                            @if ($item->product->image_url)
                                                <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" loading="lazy" class="size-full object-cover">
                                            @else
                                                <flux:icon.photo class="size-8 text-gulu-muted" />
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <h3 class="truncate text-base sm:text-lg font-medium text-gulu-ink">{{ $item->product->name }}</h3>
                                            <p class="text-xs uppercase tracking-wider text-gulu-muted font-medium">{{ $item->product->category }}</p>
                                            <p class="text-xs sm:text-sm text-gulu-muted mt-1">{{ __('Amount : :count', ['count' => $item->quantity]) }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="text-base sm:text-lg font-semibold text-gulu-ink">
                                            ฿{{ number_format((float) bcmul((string) $item->product->price, (string) $item->quantity, 2)) }}
                                        </span>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                </div>

                {{-- Right: Order Summary Sticky Card --}}
                <div class="lg:col-span-5 lg:sticky lg:top-28">
                    <div class="rounded-3xl bg-gulu-lilac-soft p-6 sm:p-8 flex flex-col gap-5">
                        <h2 class="font-display text-3xl text-gulu-ink">{{ __('Order summary') }}</h2>

                        <div class="flex justify-between items-center text-base">
                            <span class="text-gulu-muted">{{ __('Subtotal') }}</span>
                            <span class="font-medium text-gulu-ink">฿{{ number_format((float) $totalAmount) }}</span>
                        </div>

                        <div class="flex justify-between items-center text-base">
                            <span class="text-gulu-muted">{{ __('Delivery') }}</span>
                            <span class="font-medium text-gulu-ink">{{ __('Free') }}</span>
                        </div>

                        <div class="border-t border-gulu-line/70 pt-4 flex justify-between items-baseline">
                            <span class="text-base font-medium text-gulu-ink">{{ __('total price') }}</span>
                            <span class="font-display text-3xl font-semibold text-gulu-ink">฿{{ number_format((float) $totalAmount) }}</span>
                        </div>

                        <button type="submit"
                            class="mt-2 w-full rounded-full bg-gulu-ink py-4 text-center text-base font-medium text-white transition hover:bg-gulu-plum focus:outline-none focus-visible:ring-2 focus-visible:ring-gulu-lilac-deep">
                            {{ __('confirm order') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layouts::shop>

