{{-- Storefront footer (Figma: Footer). Included by layouts/shop.blade.php. --}}
<footer class="mt-16 bg-gulu-ink text-white">
    <div class="mx-auto flex max-w-[1440px] flex-col gap-12 px-6 py-14 lg:px-12">
        <div class="flex flex-col justify-between gap-10 lg:flex-row">
            <p class="font-display text-5xl font-semibold tracking-tight">gulugule shop</p>

            <div class="grid grid-cols-2 gap-10 text-[15px] sm:grid-cols-3 lg:gap-24">
                <div class="flex flex-col gap-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gulu-lilac">{{ __('Shop') }}</p>
                    <a href="{{ route('products.index', ['category' => 'bouquet']) }}" class="hover:underline">{{ __('Bouquets') }}</a>
                    <a href="{{ route('products.index', ['category' => 'single']) }}" class="hover:underline">{{ __('Single stems') }}</a>
                    <a href="{{ route('products.index', ['category' => 'potted']) }}" class="hover:underline">{{ __('Potted plants') }}</a>
                    <a href="{{ route('products.index', ['category' => 'wreath']) }}" class="hover:underline">{{ __('Wreaths') }}</a>
                </div>
                <div class="flex flex-col gap-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gulu-lilac">{{ __('Account') }}</p>
                    @auth
                        <a href="{{ route('profile.show') }}" class="hover:underline">{{ __('Profile') }}</a>
                        <a href="{{ route('orders.index') }}" class="hover:underline">{{ __('Orders') }}</a>
                        <a href="{{ route('wishlist.index') }}" class="hover:underline">{{ __('Wishlist') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="hover:underline">{{ __('Log in') }}</a>
                        <a href="{{ route('register') }}" class="hover:underline">{{ __('Sign up') }}</a>
                    @endauth
                </div>
                <div class="flex flex-col gap-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gulu-lilac">{{ __('Help') }}</p>
                    <span>{{ __('Delivery') }}</span>
                    <span>{{ __('Care guide') }}</span>
                    <span>{{ __('Contact') }}</span>
                </div>
            </div>
        </div>

        <p class="text-[13px] text-gulu-muted">© {{ date('Y') }} gulugule shop · Database project</p>
    </div>
</footer>
