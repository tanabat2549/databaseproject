{{-- Storefront header (Figma: Header). Included by layouts/shop.blade.php — do not copy per page. --}}
@php
    $cartCount = auth()->check()
        ? (int) \App\Models\CartItem::where('uid', auth()->id())->sum('quantity')
        : 0;

    $links = [
        'Bouquets' => 'bouquet',
        'Single' => 'single',
        'Potted' => 'potted',
        'Wreath' => 'wreath',
    ];
@endphp

<header class="sticky top-0 z-40 bg-white">
    <div class="bg-gulu-ink py-2.5 text-center text-xs font-medium uppercase tracking-[0.2em] text-white">
        {{ __('Free delivery on orders over ฿1,500') }} · {{ __('Fresh-cut daily') }}
    </div>

    <nav class="mx-auto flex max-w-[1440px] items-center justify-between gap-6 border-b border-gulu-line px-6 py-4 lg:px-12" aria-label="{{ __('Main') }}">
        <a href="{{ route('home') }}" class="font-display text-[34px] font-semibold leading-none tracking-tight">gulugule shop</a>

        <ul class="hidden items-center gap-9 text-base font-medium lg:flex">
            @foreach ($links as $label => $category)
                <li>
                    <a href="{{ route('products.index', ['category' => $category]) }}"
                       @class(['hover:underline', 'underline underline-offset-8' => request('category') === $category])>{{ $label }}</a>
                </li>
            @endforeach
        </ul>

        <div class="flex items-center gap-5 text-sm">
            <a href="{{ route('products.index') }}" class="flex items-center gap-1.5 hover:underline" aria-label="{{ __('Search') }}">
                <flux:icon.magnifying-glass class="size-5" />
            </a>

            @auth
                <a href="{{ route('wishlist.index') }}" class="flex items-center gap-1.5 hover:underline">
                    <flux:icon.heart class="size-5" /><span class="hidden xl:inline">{{ __('Wishlist') }}</span>
                </a>
                <a href="{{ route('cart.index') }}" class="relative flex items-center gap-1.5 hover:underline">
                    <flux:icon.shopping-bag class="size-5" /><span class="hidden xl:inline">{{ __('Cart') }}</span>
                    @if ($cartCount > 0)
                        <span class="absolute -right-2 -top-2 flex size-4 items-center justify-center rounded-full bg-gulu-ink text-[10px] font-semibold text-white">{{ $cartCount }}</span>
                    @endif
                </a>
                <a href="{{ route('orders.index') }}" class="flex items-center gap-1.5 hover:underline">
                    <flux:icon.archive-box class="size-5" /><span class="hidden xl:inline">{{ __('Status') }}</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-1.5 hover:underline">
                    <flux:icon.user class="size-5" /><span class="hidden xl:inline">{{ __('Profile') }}</span>
                </a>
            @else
                <x-button variant="outline" size="sm" :href="route('login')">{{ __('Log in') }}</x-button>
                <x-button size="sm" :href="route('register')">{{ __('Sign up') }}</x-button>
            @endauth
        </div>
    </nav>
</header>
