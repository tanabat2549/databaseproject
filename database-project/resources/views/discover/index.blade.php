<x-layouts::shop :title="__('Discover')">
    <div class="mx-auto flex max-w-md flex-col gap-6">
        <div class="text-center">
            <h1 class="font-display text-5xl font-medium tracking-tight">{{ __('Discover') }}</h1>
            <p class="mt-2 text-sm text-gulu-muted">ปัดขวาเพื่อเก็บเข้า Wishlist · ปัดซ้ายเพื่อข้าม</p>
        </div>

        {{-- Deck --}}
        <div id="deck" class="relative mx-auto aspect-[3/4] w-full select-none"></div>

        {{-- Empty state --}}
        <div id="empty" class="hidden flex-col items-center gap-4 rounded-3xl bg-gulu-mist px-6 py-16 text-center">
            <flux:icon.heart class="size-10 text-gulu-lilac-deep" />
            <p class="font-display text-3xl">{{ __('ดูครบแล้ว!') }}</p>
            <p class="text-sm text-gulu-muted">ไม่มีดอกไม้ให้ปัดเพิ่มในตอนนี้</p>
            <div class="flex flex-wrap justify-center gap-3">
                <x-button :href="route('wishlist.index')">{{ __('ดู Wishlist') }}</x-button>
                <form method="POST" action="{{ route('discover.reset') }}">
                    @csrf
                    <x-button type="submit" variant="outline">{{ __('เริ่มปัดใหม่') }}</x-button>
                </form>
            </div>
        </div>

        {{-- Buttons --}}
        <div id="controls" class="flex items-center justify-center gap-8">
            <button type="button" id="btn-skip" aria-label="Skip"
                    class="flex size-16 items-center justify-center rounded-full border border-gulu-line bg-white text-2xl shadow-sm transition hover:scale-110 hover:border-red-400 hover:text-red-500">✕</button>
            <button type="button" id="btn-like" aria-label="Like"
                    class="flex size-16 items-center justify-center rounded-full bg-gulu-ink text-2xl text-white shadow-sm transition hover:scale-110 hover:bg-gulu-plum">♥</button>
        </div>
        <p class="text-center text-xs text-gulu-muted">
            <span id="liked-count">0</span> รายการที่เก็บในรอบนี้
        </p>
    </div>

    <script>
    (function () {
        const cards = @json($cards);
        const swipeUrl = @json(route('discover.swipe', ['product' => '__ID__']));
        const csrf = @json(csrf_token());

        const deck = document.getElementById('deck');
        const empty = document.getElementById('empty');
        const controls = document.getElementById('controls');
        const likedCount = document.getElementById('liked-count');
        let index = 0, liked = 0, busy = false;

        function buildCard(p, depth) {
            const el = document.createElement('article');
            el.className = 'absolute inset-0 overflow-hidden rounded-3xl border border-gulu-line bg-white shadow-lg';
            el.style.touchAction = 'none';
            el.style.transition = 'transform .25s ease';
            el.style.transform = 'scale(' + (1 - depth * 0.05) + ') translateY(' + (depth * 14) + 'px)';
            el.style.zIndex = String(10 - depth);

            const img = document.createElement('img');
            img.src = p.image; img.alt = p.name; img.draggable = false;
            img.className = 'h-full w-full object-cover pointer-events-none';
            el.appendChild(img);

            const grad = document.createElement('div');
            grad.className = 'absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-5 pt-24 text-white';
            const cat = document.createElement('p');
            cat.className = 'text-[11px] font-semibold uppercase tracking-widest text-white/80';
            cat.textContent = p.category;
            const name = document.createElement('h2');
            name.className = 'font-display text-3xl font-medium';
            name.textContent = p.name;
            const meta = document.createElement('p');
            meta.className = 'mt-1 text-sm';
            meta.textContent = p.price + (p.reviews > 0 ? '  ·  ★ ' + p.rating.toFixed(1) + ' (' + p.reviews + ')' : '');
            const desc = document.createElement('p');
            desc.className = 'mt-1 text-xs text-white/80';
            desc.textContent = p.description;
            grad.append(cat, name, meta, desc);
            el.appendChild(grad);

            const like = document.createElement('span');
            like.dataset.badge = 'like';
            like.textContent = 'LIKE';
            like.className = 'absolute left-5 top-5 -rotate-12 rounded-lg border-4 border-green-400 px-3 py-1 text-2xl font-bold text-green-400';
            like.style.opacity = 0;
            const nope = document.createElement('span');
            nope.dataset.badge = 'nope';
            nope.textContent = 'NOPE';
            nope.className = 'absolute right-5 top-5 rotate-12 rounded-lg border-4 border-red-400 px-3 py-1 text-2xl font-bold text-red-400';
            nope.style.opacity = 0;
            el.append(like, nope);

            return el;
        }

        function render() {
            deck.innerHTML = '';
            const remaining = cards.slice(index, index + 3);

            if (remaining.length === 0) {
                deck.classList.add('hidden');
                controls.classList.add('hidden');
                empty.classList.remove('hidden');
                empty.classList.add('flex');
                return;
            }

            for (let d = remaining.length - 1; d >= 0; d--) {
                deck.appendChild(buildCard(remaining[d], d));
            }
            attachDrag(deck.lastElementChild);
        }

        function topCard() { return deck.lastElementChild; }

        function setBadges(el, dx) {
            const strength = Math.min(Math.abs(dx) / 100, 1);
            el.querySelector('[data-badge=like]').style.opacity = dx > 0 ? strength : 0;
            el.querySelector('[data-badge=nope]').style.opacity = dx < 0 ? strength : 0;
        }

        function attachDrag(el) {
            let startX = null, dx = 0;

            el.addEventListener('pointerdown', function (e) {
                if (busy) return;
                startX = e.clientX; dx = 0;
                el.setPointerCapture(e.pointerId);
                el.style.transition = 'none';
            });
            el.addEventListener('pointermove', function (e) {
                if (startX === null) return;
                dx = e.clientX - startX;
                el.style.transform = 'translateX(' + dx + 'px) rotate(' + (dx / 20) + 'deg)';
                setBadges(el, dx);
            });
            function release() {
                if (startX === null) return;
                startX = null;
                el.style.transition = 'transform .25s ease';
                if (Math.abs(dx) > 100) {
                    commit(dx > 0 ? 'like' : 'skip');
                } else {
                    el.style.transform = '';
                    setBadges(el, 0);
                }
            }
            el.addEventListener('pointerup', release);
            el.addEventListener('pointercancel', release);
        }

        function commit(direction) {
            if (busy) return;
            const el = topCard();
            const product = cards[index];
            if (!el || !product) return;
            busy = true;

            const x = direction === 'like' ? 900 : -900;
            el.style.transition = 'transform .3s ease, opacity .3s ease';
            el.style.transform = 'translateX(' + x + 'px) rotate(' + (x / 20) + 'deg)';
            el.style.opacity = 0;

            fetch(swipeUrl.replace('__ID__', product.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ direction: direction }),
            }).then(function (res) {
                if (!res.ok) throw new Error('swipe failed');
                if (direction === 'like') { liked++; likedCount.textContent = liked; }
            }).catch(function () {
                alert('บันทึกไม่สำเร็จ กรุณาลองใหม่');
            });

            setTimeout(function () {
                index++;
                busy = false;
                render();
            }, 280);
        }

        document.getElementById('btn-like').addEventListener('click', function () { commit('like'); });
        document.getElementById('btn-skip').addEventListener('click', function () { commit('skip'); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowRight') commit('like');
            if (e.key === 'ArrowLeft') commit('skip');
        });

        render();
    })();
    </script>
</x-layouts::shop>