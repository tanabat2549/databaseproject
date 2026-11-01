<x-layouts.shop>

    <x-slot:title>gulugule shop - Speak in blooms</x-slot:title>

    <x-slot:metaDescription>ช่อดอกไม้สด ดอกเดี่ยว ไม้กระถาง และพวงหรีด จัดส่งตรงเวลา</x-slot:metaDescription>



     <!-- Hero Slider -->
    <section id="hero-slider" style="touch-action: pan-y;"
             class="relative w-full h-[85vh] min-h-[600px] max-h-[800px] overflow-hidden bg-stone-900 group select-none">

        @if($bestSellers->count() > 0)

            @foreach($bestSellers as $index => $product)
                <img data-slide-img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                     referrerpolicy="no-referrer" draggable="false"
                     style="opacity: {{ $index === 0 ? 1 : 0 }};"
                     class="absolute inset-0 w-full h-full object-cover object-center transition-opacity duration-700 ease-in-out pointer-events-none">
            @endforeach

            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent pointer-events-none"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/20 pointer-events-none"></div>

            <div class="absolute inset-0 max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 flex items-center pointer-events-none">
                <div class="max-w-xl w-full text-white z-10 pt-10 flex flex-col items-start">

                    <div class="inline-flex items-center gap-2 rounded-full bg-white/20 backdrop-blur-md px-4 py-1.5 mb-4 border border-white/30 shadow-lg">
                        <span class="w-2 h-2 rounded-full bg-purple-300 animate-pulse"></span>
                        <p class="text-xs sm:text-sm font-medium tracking-wide text-purple-100">Because you deserve fresh blooms.</p>
                    </div>

                    <h1 class="font-serif font-light uppercase tracking-tight text-white leading-[0.92] text-4xl sm:text-7xl lg:text-8xl drop-shadow-md mb-4">
                        Speak<br><span class="italic font-normal tracking-normal text-purple-100 ml-6 sm:ml-16">in blooms</span>
                    </h1>

                    <div class="grid w-full">
                        @foreach($bestSellers as $index => $product)
                            <div data-slide-info
                                 style="opacity: {{ $index === 0 ? 1 : 0 }}; pointer-events: {{ $index === 0 ? 'auto' : 'none' }};"
                                 class="col-start-1 row-start-1 flex flex-col items-start transition-opacity duration-500">

                                <div class="flex items-center gap-2.5 bg-black/70 backdrop-blur-md px-4 py-2 rounded-2xl border border-white/20 mb-6 w-fit max-w-full">
                                    <span class="text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-md bg-purple-600 font-bold text-white shrink-0">{{ $product->category }}</span>
                                    <span class="text-sm font-semibold text-white min-w-0 flex-1 truncate">{{ $product->name }}</span>
                                    <span class="text-sm font-bold text-amber-300 whitespace-nowrap shrink-0">{{ $product->formatted_price }}</span>
                                </div>

                                <div class="flex flex-wrap items-center gap-4">
                                    <a href="{{ route('products.show', $product) }}" draggable="false"
                                       class="px-8 py-3.5 rounded-full bg-white text-stone-900 text-xs font-bold uppercase tracking-wider hover:bg-purple-100 hover:scale-105 transition-all shadow-xl">
                                        Shop this item
                                    </a>
                                    <a href="{{ route('products.index') }}" draggable="false"
                                       class="px-8 py-3.5 rounded-full bg-black/40 backdrop-blur-md border border-white/40 text-white text-xs font-bold uppercase tracking-wider hover:bg-white/30 hover:border-white transition-all shadow-lg">
                                        All flowers ({{ $totalProducts }})
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>

            <button type="button" data-slide-prev aria-label="Previous slide"
                    class="absolute left-2 sm:left-8 top-1/2 -translate-y-1/2 p-2 sm:p-4 rounded-full bg-white/10 backdrop-blur-md text-white border border-white/20 hover:bg-white/30 transition-all z-30 shadow-2xl">
                <svg class="w-5 h-5 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" data-slide-next aria-label="Next slide"
                    class="absolute right-2 sm:right-8 top-1/2 -translate-y-1/2 p-2 sm:p-4 rounded-full bg-white/10 backdrop-blur-md text-white border border-white/20 hover:bg-white/30 transition-all z-30 shadow-2xl">
                <svg class="w-5 h-5 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7"/></svg>
            </button>

            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-2.5 z-30 bg-black/40 backdrop-blur-md px-4 py-2 rounded-full border border-white/15">
                @foreach($bestSellers as $index => $product)
                    <button type="button" data-slide-dot="{{ $index }}"
                            class="h-2 rounded-full transition-all duration-300 {{ $index === 0 ? 'w-8 bg-white' : 'w-2 bg-white/40 hover:bg-white/70' }}"
                            aria-label="Go to slide {{ $index + 1 }}"></button>
                @endforeach
            </div>
        @endif
    </section>

    <script>
        (function () {
            function initHero() {
                var root = document.getElementById('hero-slider');
                if (!root || root.dataset.ready) return;
                var imgs = root.querySelectorAll('[data-slide-img]');
                var infos = root.querySelectorAll('[data-slide-info]');
                var dots = root.querySelectorAll('[data-slide-dot]');
                var total = imgs.length;
                if (total < 2) return;
                root.dataset.ready = '1';
                var current = 0, paused = false;

                function show(i) {
                    current = (i + total) % total;
                    imgs.forEach(function (el, n) { el.style.opacity = n === current ? 1 : 0; });
                    infos.forEach(function (el, n) {
                        el.style.opacity = n === current ? 1 : 0;
                        el.style.pointerEvents = n === current ? 'auto' : 'none';
                    });
                    dots.forEach(function (el, n) {
                        var on = n === current;
                        el.classList.toggle('w-8', on);
                        el.classList.toggle('bg-white', on);
                        el.classList.toggle('w-2', !on);
                        el.classList.toggle('bg-white/40', !on);
                    });
                }

                root.querySelector('[data-slide-prev]').addEventListener('click', function () { show(current - 1); });
                root.querySelector('[data-slide-next]').addEventListener('click', function () { show(current + 1); });
                dots.forEach(function (el) {
                    el.addEventListener('click', function () { show(parseInt(el.dataset.slideDot, 10)); });
                });

                var startX = null;
                root.addEventListener('pointerdown', function (e) { if (!e.target.closest('button, a')) startX = e.clientX; });
                root.addEventListener('pointerup', function (e) {
                    if (startX === null) return;
                    var dx = e.clientX - startX; startX = null;
                    if (Math.abs(dx) > 50) show(dx < 0 ? current + 1 : current - 1);
                });

                root.addEventListener('mouseenter', function () { paused = true; });
                root.addEventListener('mouseleave', function () { paused = false; });
                setInterval(function () { if (!paused && !document.hidden) show(current + 1); }, 6000);
            }
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initHero);
            else initHero();
            document.addEventListener('livewire:navigated', initHero);
        })();
    </script>



    <!-- Shop by category -->

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16">

        <div class="flex items-end justify-between mb-8">

            <h2 class="font-serif text-4xl lg:text-5xl font-medium">Shop by category</h2>

            <a href="{{ route('products.index') }}"

               class="px-5 py-2 rounded-full border border-[#1c0f2a]/30 text-xs font-semibold hover:bg-[#1c0f2a] hover:text-white transition-colors">

                View all

            </a>

        </div>



        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">

            @foreach($categories as $cat)

                <a href="{{ route('products.index', ['category' => $cat['key']]) }}"

                   class="group relative aspect-[3/4] rounded-3xl overflow-hidden bg-[#ece6f2]">

                    @if($cat['image'])

                        <img src="{{ $cat['image'] }}" alt="{{ $cat['label'] }}"

                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

                    @endif

                    <span class="absolute bottom-4 left-4 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white text-[11px] font-semibold shadow-sm">

                        {{ $cat['label'] }}

                        <span class="text-[#7a4fa3]">{{ $cat['count'] }}</span>

                    </span>

                </a>

            @endforeach

        </div>

    </section>



    <!-- Best-selling bouquets -->

    @if($bestSellers->count() > 0)

        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20">

            <div class="flex items-end justify-between mb-8">

                <h2 class="font-serif text-4xl lg:text-5xl font-medium">Best-selling bouquets</h2>

                <a href="{{ route('products.index', ['category' => 'bouquet']) }}"

                   class="px-5 py-2 rounded-full bg-[#1c0f2a] text-white text-xs font-semibold hover:bg-[#33204a] transition-colors">

                    View all

                </a>

            </div>



            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">

                @foreach($bestSellers as $product)

                    <a href="{{ route('products.show', $product) }}" class="group block">

                        <div class="relative aspect-square rounded-3xl overflow-hidden bg-[#ece6f2]">

                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"

                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 {{ $product->stock < 1 ? 'grayscale opacity-70' : '' }}">

                            @if($product->stock < 1)

                                <span class="absolute bottom-3 left-3 px-2.5 py-1 rounded-full bg-[#1c0f2a]/90 text-white text-[10px] font-medium">Sold out</span>

                            @endif

                        </div>

                        <div class="mt-3 flex items-baseline justify-between gap-3">

                            <h3 class="text-sm font-semibold truncate font-sans">{{ $product->name }}</h3>

                            <span class="text-sm font-bold whitespace-nowrap">{{ $product->formatted_price }}</span>

                        </div>

                        <p class="text-[11px] uppercase tracking-wider text-zinc-400 mt-0.5">{{ $product->category }}</p>

                    </a>

                @endforeach

            </div>

        </section>

    @endif



</x-layouts.shop> 