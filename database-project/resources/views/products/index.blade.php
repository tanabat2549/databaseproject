<x-layouts.shop>
    <x-slot:title>All flowers - gulugule shop</x-slot:title>
    <x-slot:metaDescription>เลือกซื้อช่อดอกไม้สด ดอกเดี่ยว ไม้กระถาง และพวงหรีด</x-slot:metaDescription>

    @php
        $categoryOptions = [
            'all' => 'All flowers',
            'bouquet' => 'Bouquets',
            'single' => 'Single stems',
            'potted' => 'Potted plants',
            'wreath' => 'Wreaths',
        ];
        $currentCategory = empty($selectedCategory) ? 'all' : $selectedCategory;
        $heading = $categoryOptions[$currentCategory] ?? 'All flowers';

        // ลิงก์ช่วงราคาแบบสำเร็จรูป (คงตัวกรองอื่นไว้)
        $baseQuery = array_filter([
            'search' => $search,
            'category' => $currentCategory !== 'all' ? $currentCategory : null,
            'sort' => $sort !== 'latest' ? $sort : null,
            'in_stock' => $inStockOnly ? 1 : null,
        ]);
        $pricePresets = [
            ['label' => 'Under ฿500', 'min' => null, 'max' => 500],
            ['label' => '฿500 – ฿1,000', 'min' => 500, 'max' => 1000],
            ['label' => 'Over ฿1,000', 'min' => 1000, 'max' => null],
        ];
        $hasActiveFilter = $search || $currentCategory !== 'all' || $inStockOnly || $minPrice !== null || $maxPrice !== null;
    @endphp

    <!-- Small hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-[#e9def0] via-[#d6c4e3] to-[#c9b0db]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14 relative">
            <div class="relative z-10 max-w-md">
                <span class="text-[11px] font-semibold tracking-[0.2em] uppercase text-[#7a4fa3]">New season</span>
                <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl font-medium leading-[1.05] mt-2">
                    Fresh flowers,<br>for every mood.
                </h1>
                <a href="{{ route('products.index', ['category' => 'bouquet']) }}"
                   class="inline-block mt-5 px-5 py-2.5 rounded-full bg-[#1c0f2a] text-white text-xs font-semibold hover:bg-[#33204a] transition-colors">
                    Shop bouquets
                </a>
            </div>
            <div class="hidden sm:block absolute -right-10 -top-10 w-72 h-72 rounded-full bg-white/30"></div>
            <div class="hidden sm:block absolute right-24 top-8 w-40 h-40 rounded-full bg-[#b794d0]/60"></div>
            <div class="hidden sm:block absolute right-6 bottom-2 w-28 h-28 rounded-full bg-[#f4d9e6]/80"></div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        @if($errors->any())
            <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-sm">
                @foreach($errors->all() as $error)
                    <p>⚠️ {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="grid lg:grid-cols-[220px_1fr] gap-10">

            <!-- Sidebar filter -->
            <aside>
                <div class="flex items-center gap-2 text-sm font-semibold mb-5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18l-7 8v6l-4 2v-8L3 4z"/></svg>
                    Filter
                </div>

                <form id="filter-form" action="{{ route('products.index') }}" method="GET" class="space-y-7">

                    <!-- Search -->
                    <div id="search">
                        <div class="flex items-center gap-2 rounded-full border border-[#d9c9e3] px-3 py-1.5 focus-within:ring-2 focus-within:ring-[#b794d0]">
                            <input type="text" name="search" id="input-search-product" value="{{ $search }}" maxlength="100"
                                   placeholder="Search flowers"
                                   class="w-full bg-transparent border-0 text-xs focus:outline-none placeholder-zinc-400">
                            <button type="submit" id="btn-submit-search" class="text-[#7a4fa3] text-xs font-semibold">Go</button>
                        </div>
                        @if($search)
                            <a href="{{ route('products.index', array_diff_key($baseQuery, ['search' => 1])) }}"
                               id="btn-clear-search" class="inline-block mt-1.5 text-[11px] text-zinc-400 hover:text-[#7a4fa3]">✕ Clear search</a>
                        @endif
                    </div>

                    <!-- Category -->
                    <div>
                        <h3 class="text-[11px] font-semibold tracking-[0.2em] uppercase text-zinc-400 mb-3">Category</h3>
                        <div class="space-y-2.5">
                            @foreach($categoryOptions as $key => $label)
                                <label class="flex items-center gap-2.5 text-sm cursor-pointer">
                                    <input type="radio" name="category" value="{{ $key }}"
                                           onchange="this.form.submit()"
                                           {{ $currentCategory === $key ? 'checked' : '' }}
                                           class="text-[#7a4fa3] focus:ring-[#b794d0]">
                                    <span>{{ $label }} <span class="text-zinc-400">({{ $categoryCounts[$key] ?? 0 }})</span></span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Price -->
                    <div>
                        <h3 class="text-[11px] font-semibold tracking-[0.2em] uppercase text-zinc-400 mb-3">Price</h3>
                        <div class="space-y-2 mb-3">
                            @foreach($pricePresets as $preset)
                                @php
                                    $active = $minPrice === ($preset['min'] === null ? null : (float) $preset['min'])
                                        && $maxPrice === ($preset['max'] === null ? null : (float) $preset['max']);
                                @endphp
                                <a href="{{ route('products.index', array_merge($baseQuery, array_filter(['min_price' => $preset['min'], 'max_price' => $preset['max']]))) }}"
                                   class="block text-sm {{ $active ? 'font-semibold text-[#7a4fa3]' : 'text-[#1c0f2a]/80 hover:text-[#7a4fa3]' }}">
                                    {{ $active ? '● ' : '○ ' }}{{ $preset['label'] }}
                                </a>
                            @endforeach
                        </div>
                        <div class="flex items-center gap-1.5">
                            <input type="number" name="min_price" id="input-min-price" value="{{ $minPrice }}" min="0" step="1"
                                   placeholder="Min"
                                   class="w-full text-xs rounded-lg border border-[#d9c9e3] px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-[#b794d0]">
                            <span class="text-zinc-400">–</span>
                            <input type="number" name="max_price" id="input-max-price" value="{{ $maxPrice }}" min="0" step="1"
                                   placeholder="Max"
                                   class="w-full text-xs rounded-lg border border-[#d9c9e3] px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-[#b794d0]">
                        </div>
                        <button type="submit" id="btn-apply-filter"
                                class="mt-2 w-full py-1.5 rounded-full bg-[#1c0f2a] text-white text-xs font-semibold hover:bg-[#33204a] transition-colors">
                            Apply price
                        </button>
                    </div>

                    <!-- Availability -->
                    <div>
                        <h3 class="text-[11px] font-semibold tracking-[0.2em] uppercase text-zinc-400 mb-3">Availability</h3>
                        <label class="flex items-center gap-2.5 text-sm cursor-pointer">
                            <input type="checkbox" name="in_stock" value="1" onchange="this.form.submit()"
                                   {{ $inStockOnly ? 'checked' : '' }}
                                   class="rounded text-[#7a4fa3] focus:ring-[#b794d0]">
                            In stock only
                        </label>
                    </div>

                    @if($hasActiveFilter)
                        <a href="{{ route('products.index') }}" id="btn-clear-all"
                           class="inline-block text-xs text-[#7a4fa3] hover:underline">Clear all filters</a>
                    @endif
                </form>
            </aside>

            <!-- Product listing -->
            <div>
                <div class="flex items-center justify-between gap-4 mb-6">
                    <h2 class="font-serif text-3xl lg:text-4xl font-medium">
                        {{ $heading }} <span class="text-zinc-400 text-2xl">({{ $products->total() }})</span>
                    </h2>
                    <div class="flex items-center gap-2 text-xs">
                        <label for="select-sort" class="text-zinc-400 hidden sm:inline">Sort:</label>
                        <select name="sort" id="select-sort" form="filter-form" onchange="document.getElementById('filter-form').submit()"
                                class="rounded-full border border-[#d9c9e3] bg-white px-4 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#b794d0] cursor-pointer">
                            <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Newest</option>
                            <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>Top rated</option>
                            <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Price: low to high</option>
                            <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Price: high to low</option>
                            <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Name A–Z</option>
                        </select>
                    </div>
                </div>

                @if($products->count() > 0)
                    <div class="grid grid-cols-2 xl:grid-cols-3 gap-4 lg:gap-6">
                        @foreach($products as $product)
                            @php
                                $inWishlist = in_array($product->id, $wishlistIds ?? [], true);
                                $avg = $product->averageRating();
                            @endphp
                            <div class="group">
                                <div class="relative aspect-square rounded-3xl overflow-hidden bg-[#ece6f2]">
                                    <a href="{{ route('products.show', $product) }}">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 {{ $product->stock < 1 ? 'grayscale opacity-70' : '' }}">
                                    </a>

                                    @if($product->stock < 1)
                                        <span class="absolute bottom-3 left-3 px-2.5 py-1 rounded-full bg-[#1c0f2a]/90 text-white text-[10px] font-medium">Sold out</span>
                                    @elseif($product->stock <= 3)
                                        <span class="absolute bottom-3 left-3 px-2.5 py-1 rounded-full bg-white/90 text-[#7a4fa3] text-[10px] font-semibold">Only {{ $product->stock }} left</span>
                                    @endif

                                    <!-- Wishlist heart -->
                                    <div class="absolute top-3 right-3">
                                        @auth
                                            @if($inWishlist)
                                                <form action="{{ route('wishlist.destroy', $product) }}" method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" title="Remove from wishlist"
                                                            class="w-9 h-9 rounded-full bg-white/90 text-[#7a4fa3] flex items-center justify-center shadow-sm hover:bg-white">
                                                        <svg class="w-4 h-4" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('wishlist.store', $product) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" title="Add to wishlist"
                                                            class="w-9 h-9 rounded-full bg-white/90 text-zinc-500 hover:text-[#7a4fa3] flex items-center justify-center shadow-sm hover:bg-white">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <a href="{{ route('login') }}" title="Log in to save"
                                               class="w-9 h-9 rounded-full bg-white/90 text-zinc-500 hover:text-[#7a4fa3] flex items-center justify-center shadow-sm hover:bg-white">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                            </a>
                                        @endauth
                                    </div>

                                    <!-- Quick add -->
                                    @if($product->stock > 0)
                                        <div class="absolute bottom-3 right-3">
                                            @auth
                                                <form action="{{ route('cart.store', $product) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" title="Add to cart"
                                                            class="w-9 h-9 rounded-full bg-[#1c0f2a] text-white flex items-center justify-center shadow-sm hover:bg-[#33204a] transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('login') }}" title="Log in to add to cart"
                                                   class="w-9 h-9 rounded-full bg-[#1c0f2a] text-white flex items-center justify-center shadow-sm hover:bg-[#33204a] transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                </a>
                                            @endauth
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-3 flex items-baseline justify-between gap-3">
                                    <h3 class="text-sm font-semibold truncate font-sans">
                                        <a href="{{ route('products.show', $product) }}" class="hover:text-[#7a4fa3]">{{ $product->name }}</a>
                                    </h3>
                                    <span class="text-sm font-bold whitespace-nowrap">{{ $product->formatted_price }}</span>
                                </div>
                                <p class="text-[11px] uppercase tracking-wider text-zinc-400 mt-0.5">{{ $product->category }}</p>
                                <div class="flex items-center gap-1.5 mt-1.5 text-xs">
                                    <span class="text-amber-400 tracking-tight">
                                        @for($i = 1; $i <= 5; $i++){{ $i <= round($avg) ? '★' : '☆' }}@endfor
                                    </span>
                                    <span class="text-zinc-400">{{ $product->reviewsCount() > 0 ? '('.$product->reviewsCount().')' : 'No reviews yet' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-12">{{ $products->links() }}</div>
                @else
                    <div class="text-center py-20 rounded-3xl bg-[#f5f1f8] max-w-lg mx-auto p-8">
                        <span class="text-5xl block mb-4">🥀</span>
                        <h3 class="font-serif text-2xl font-semibold">No flowers found</h3>
                        <p class="text-sm text-zinc-500 mt-2">Try a different search, price range or category.</p>
                        <a href="{{ route('products.index') }}"
                           class="inline-block mt-6 px-6 py-2.5 rounded-full bg-[#1c0f2a] text-white text-xs font-semibold hover:bg-[#33204a] transition-colors">
                            View all flowers
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.shop>