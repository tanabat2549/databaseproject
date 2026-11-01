<x-layouts.shop>
    <x-slot:title>{{ $product->name }}</x-slot:title>
    <x-slot:metaDescription>{{ Str::limit($product->description, 160) }}</x-slot:metaDescription>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- Breadcrumb --}}
        <nav class="mb-8 flex items-center gap-2 text-xs text-gulu-muted" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:underline">Home</a>
            <span>/</span>
            <a href="{{ route('products.index') }}" class="hover:underline">Flowers</a>
            <span>/</span>
            <a href="{{ route('products.index', ['category' => $product->category]) }}" class="hover:underline">{{ $product->category_label }}</a>
            <span>/</span>
            <span class="text-gulu-ink font-medium">{{ $product->name }}</span>
        </nav>

        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20">

            {{-- Product Image --}}
            <div class="relative aspect-square rounded-3xl overflow-hidden bg-[#ece6f2]">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                     class="w-full h-full object-cover {{ $product->stock < 1 ? 'grayscale opacity-70' : '' }}">
                @if ($product->stock < 1)
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="px-6 py-3 rounded-full bg-white/90 text-gulu-ink font-semibold text-sm shadow-lg">Sold out</span>
                    </div>
                @endif
            </div>

            {{-- Product Info --}}
            <div class="flex flex-col gap-6">

                {{-- Category & Name --}}
                <div>
                    <span class="text-[11px] font-semibold tracking-[0.2em] uppercase text-[#7a4fa3]">{{ $product->category_label }}</span>
                    <h1 class="font-serif text-4xl lg:text-5xl font-medium mt-1 leading-tight">{{ $product->name }}</h1>
                </div>

                {{-- Rating --}}
                <div class="flex items-center gap-3">
                    <x-rating-stars :rating="$product->averageRating()" :count="$product->reviewsCount()" />
                </div>

                {{-- Price --}}
                <p class="text-3xl font-bold">{{ $product->formatted_price }}</p>

                {{-- Description --}}
                @if ($product->description)
                    <p class="text-gulu-muted leading-relaxed text-[15px]">{{ $product->description }}</p>
                @endif

                {{-- Quantity + Add to Cart --}}
                @if ($product->stock > 0)
                    <form method="POST" action="{{ route('cart.store', $product) }}" class="flex flex-col gap-4">
                        @csrf
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-medium">Quantity</span>
                            <x-quantity-stepper name="quantity" :value="1" :max="$product->stock" />
                            <span class="text-sm font-medium text-gulu-success">{{ $product->stock }} in stock</span>
                        </div>
                        <button type="submit"
                                class="w-full rounded-full bg-gulu-ink py-3.5 text-sm font-semibold text-white shadow-md transition-colors hover:bg-[#33204a]">
                            add to cart
                        </button>
                    </form>
                @else
                    <p class="text-sm font-medium text-red-500">✕ Out of stock</p>
                    <button disabled class="w-full cursor-not-allowed rounded-full bg-zinc-200 py-3.5 text-sm font-semibold text-zinc-400">
                        Out of stock
                    </button>
                @endif

                {{-- Wishlist --}}
                <x-wishlist-button :product="$product" class="w-full" />

                {{-- Service highlights --}}
                <ul class="grid gap-3 rounded-2xl bg-[#f5f1f8] p-5 text-sm sm:grid-cols-3">
                    @foreach (['On-time delivery', 'Free greeting card', 'Fresh-cut daily'] as $perk)
                        <li class="flex items-center gap-2">
                            <span class="text-gulu-success">✓</span> {{ $perk }}
                        </li>
                    @endforeach
                </ul>

            </div>
        </div>

        {{-- Reviews Section --}}
        <section id="reviews" class="mt-20">

            <h2 class="font-serif text-3xl font-medium mb-6">Customer reviews</h2>

            {{-- Rating summary --}}
            <div class="mb-10 grid gap-6 rounded-2xl bg-[#f5f1f8] p-6 sm:grid-cols-[auto_1fr] sm:items-center sm:gap-12">
                <div class="text-center sm:text-left">
                    <div class="font-serif text-6xl font-medium leading-none">{{ number_format($product->averageRating(), 1) }}</div>
                    <div class="mt-3"><x-rating-stars :rating="$product->averageRating()" :size="20" /></div>
                    <p class="mt-1 text-sm text-gulu-muted">
                        {{ $product->reviewsCount() }} {{ Str::plural('review', $product->reviewsCount()) }}
                    </p>
                </div>

                <div class="flex flex-col gap-2">
                    @foreach ($ratingBreakdown as $star => $data)
                        <div class="flex items-center gap-3 text-xs text-gulu-muted">
                            <span class="w-8 text-right">{{ $star }} ★</span>
                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-gulu-line">
                                <div class="h-full rounded-full bg-gulu-gold" style="width: {{ $data['percentage'] }}%"></div>
                            </div>
                            <span class="w-6 text-right">{{ $data['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Write a Review Form --}}
            @auth
                @if ($canReview)
                    <div class="mb-10 rounded-2xl border border-gulu-line p-6 bg-[#faf8fc]">
                        <h3 class="font-semibold text-lg mb-4">Write a review</h3>
                        <form method="POST" action="{{ route('reviews.store', $product) }}" class="flex flex-col gap-5">
                            @csrf

                            @if ($errors->any())
                                <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                                    @foreach ($errors->all() as $error)
                                        <p>{{ $error }}</p>
                                    @endforeach
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium mb-2">How would you rate this flower?</label>
                                <x-rating-circles name="rating" :value="old('rating', 5)" />
                            </div>

                            <div>
                                <label for="comment" class="block text-sm font-medium mb-2">Comment (optional)</label>
                                <textarea name="comment" id="comment" rows="4" maxlength="1000"
                                          class="w-full rounded-2xl border border-gulu-line px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#b794d0] resize-none"
                                          placeholder="Share what you liked: freshness, packaging, delivery.">{{ old('comment') }}</textarea>
                            </div>

                            <button type="submit"
                                    class="self-start px-6 py-2.5 rounded-full bg-gulu-ink text-white text-sm font-semibold hover:bg-[#33204a] transition-colors">
                                ✓ Submit review
                            </button>
                        </form>
                    </div>
                @elseif ($hasReviewed)
                    <div class="mb-8 rounded-xl bg-gulu-success/10 px-5 py-3 text-sm text-gulu-success">
                        ✓ You've already reviewed this product.
                    </div>
                @else
                    <div class="mb-8 rounded-xl border border-gulu-line bg-[#faf8fc] px-5 py-4 text-sm text-gulu-muted">
                        You can write a review only after your order has been delivered.
                        <a href="{{ route('orders.index') }}" class="font-semibold text-[#7a4fa3] hover:underline">View my orders</a>
                    </div>
                @endif
            @else
                <div class="mb-8 rounded-xl bg-[#faf8fc] border border-gulu-line px-5 py-4 text-sm text-gulu-muted">
                    <a href="{{ route('login') }}" class="font-semibold text-[#7a4fa3] hover:underline">Log in</a> to write a review.
                </div>
            @endauth

            {{-- Review List --}}
            @forelse ($product->reviews as $review)
                <div class="mb-3 rounded-2xl bg-[#f5f1f8] p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-full bg-[#e3d8ee] text-sm font-semibold text-[#7a4fa3]">
                                {{ mb_strtoupper(mb_substr($review->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold">{{ $review->user->name ?? 'User' }}</p>
                                <p class="text-xs text-gulu-muted">{{ $review->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                        <x-rating-stars :rating="$review->rating" :size="14" />
                    </div>
                    @if ($review->comment)
                        <p class="mt-3 text-sm leading-relaxed text-gulu-ink">{{ $review->comment }}</p>
                    @endif
                </div>
            @empty
                <p class="py-12 text-center text-sm text-gulu-muted">No reviews yet. Be the first!</p>
            @endforelse
        </section>

        {{-- Related Products --}}
        @if ($relatedProducts->count() > 0)
            <section class="mt-20">
                <div class="flex items-end justify-between mb-8">
                    <h2 class="font-serif text-3xl font-medium">You may also like</h2>
                    <a href="{{ route('products.index', ['category' => $product->category]) }}"
                       class="px-5 py-2 rounded-full border border-gulu-line text-xs font-semibold hover:bg-gulu-ink hover:text-white transition-colors">
                        View all
                    </a>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
                    @foreach ($relatedProducts as $related)
                        <x-product-card :product="$related" />
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</x-layouts.shop>