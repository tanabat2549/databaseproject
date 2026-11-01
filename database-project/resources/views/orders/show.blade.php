<x-layouts::shop :title="__('Order #:id', ['id' => $order->id])">
    <div class="flex flex-col gap-6">
        <div>
            <x-button variant="outline" size="sm" :href="route('orders.index')" class="w-fit">
                <flux:icon.arrow-left class="size-4" />{{ __('Back to Orders') }}
            </x-button>
        </div>

        {{-- Order Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gulu-line/60 pb-6">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="font-display text-4xl sm:text-5xl font-medium tracking-tight text-gulu-ink">
                        {{ __('Order #:id', ['id' => $order->id]) }}
                    </h1>
                    <x-status-badge :status="$order->status" />
                </div>
                <p class="mt-2 text-sm text-gulu-muted">
                    {{ __('สั่งซื้อเมื่อวันที่ :date', ['date' => $order->created_at->format('j M Y, H:i น.')]) }}
                </p>
            </div>

            <div class="text-left sm:text-right">
                <p class="text-xs uppercase tracking-wider text-gulu-muted font-medium">{{ __('ยอดรวมสุทธิ') }}</p>
                <p class="font-display text-3xl font-semibold text-gulu-ink">
                    ฿{{ number_format((float) $order->total_amount, 2) }}
                </p>
            </div>
        </div>

        {{-- Pending Confirmation Action Banner --}}
        @if ($order->isPending())
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-3xl border border-gulu-gold/40 bg-gulu-gold/15 p-6">
                <div class="flex items-start gap-4">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-gulu-gold/30 text-gulu-ink">
                        <flux:icon.truck class="size-5" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gulu-ink">{{ __('สินค้าอยู่ระหว่างการจัดส่ง (Pending)') }}</h2>
                        <p class="text-sm text-gulu-muted mt-0.5">
                            {{ __('กำหนดวันจัดส่ง: :date · เมื่อได้รับดอกไม้แล้ว กรุณากดยืนยันรับสินค้าเพื่อปลดล็อกการเขียนรีวิว', ['date' => $order->delivery_date->format('j M Y')]) }}
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('orders.receive', $order) }}"
                      onsubmit="return confirm('{{ __('ยืนยันว่าคุณได้รับดอกไม้สำหรับคำสั่งซื้อ #:id เรียบร้อยแล้ว?', ['id' => $order->id]) }}')">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-gulu-ink px-7 py-3 text-sm font-medium text-white transition hover:bg-gulu-plum shadow-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-gulu-lilac-deep">
                        <flux:icon.check class="size-4" />
                        {{ __('ฉันได้รับสินค้าแล้ว') }}
                    </button>
                </form>
            </div>
        @else
            <div class="flex items-center gap-3 rounded-2xl bg-gulu-success/15 px-5 py-3.5 text-sm text-gulu-success">
                <flux:icon.check-circle class="size-5 shrink-0" />
                <span>{{ __('จัดส่งสำเร็จเรียบร้อยแล้วเมื่อวันที่ :date ขอบคุณที่เลือกใช้บริการ gulugule shop', ['date' => $order->delivery_date->format('j M Y')]) }}</span>
            </div>
        @endif

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Left: Shipping Details & Items breakdown & Reviews --}}
            <div class="lg:col-span-7 flex flex-col gap-8">
                {{-- Shipping Information Card --}}
                <section class="rounded-3xl border border-gulu-line bg-white p-6 sm:p-7 flex flex-col gap-4">
                    <h2 class="font-display text-2xl text-gulu-ink">{{ __('ข้อมูลการจัดส่ง') }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="flex flex-col gap-1">
                            <span class="text-xs uppercase tracking-wider text-gulu-muted font-medium">{{ __('ที่อยู่สำหรับจัดส่ง') }}</span>
                            <span class="text-base text-gulu-ink leading-relaxed">{{ $order->shipping_address }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs uppercase tracking-wider text-gulu-muted font-medium">{{ __('วันที่ต้องการให้จัดส่ง') }}</span>
                            <span class="text-base text-gulu-ink">{{ $order->delivery_date->format('j M Y') }}</span>
                        </div>
                    </div>
                </section>

                {{-- Itemized Breakdown Table --}}
                <section class="rounded-3xl border border-gulu-line bg-white p-6 sm:p-7 flex flex-col gap-5">
                    <h2 class="font-display text-2xl text-gulu-ink">{{ __('รายการสินค้าที่สั่งซื้อ') }}</h2>

                    <div class="flex flex-col divide-y divide-gulu-line/50">
                        @foreach ($order->items as $item)
                            <div class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="size-16 shrink-0 overflow-hidden rounded-2xl bg-gulu-lilac-soft flex items-center justify-center">
                                        @if ($item->product?->image_url)
                                            <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" loading="lazy" class="size-full object-cover">
                                        @else
                                            <flux:icon.photo class="size-6 text-gulu-muted" />
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        @if ($item->product)
                                            <a href="{{ route('products.show', $item->product) }}" class="truncate text-base font-medium text-gulu-ink hover:underline">
                                                {{ $item->product->name }}
                                            </a>
                                            <p class="text-xs uppercase tracking-wider text-gulu-muted font-medium">{{ $item->product->category }}</p>
                                        @else
                                            <span class="truncate text-base font-medium text-gulu-ink">{{ __('สินค้าที่ไม่มีในระบบแล้ว') }}</span>
                                        @endif
                                        <p class="text-xs sm:text-sm text-gulu-muted mt-1">
                                            {{ __('ราคา ณ ตอนซื้อ:') }} ฿{{ number_format((float) $item->unit_price, 2) }} × {{ $item->quantity }} {{ __('ชิ้น') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="text-base sm:text-lg font-semibold text-gulu-ink">
                                        ฿{{ number_format((float) $item->subtotal, 2) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- Product Reviews Section --}}
                <section class="rounded-3xl border border-gulu-line bg-white p-6 sm:p-7 flex flex-col gap-6">
                    <div>
                        <h2 class="font-display text-2xl text-gulu-ink">{{ __('รีวิวสินค้าในคำสั่งซื้อนี้') }}</h2>
                        <p class="text-sm text-gulu-muted mt-1">
                            @if ($order->isDelivered())
                                {{ __('ร่วมแบ่งปันความประทับใจสำหรับดอกไม้แต่ละชนิดในคำสั่งซื้อนี้') }}
                            @else
                                {{ __('คุณสามารถเขียนรีวิวได้หลังจากได้รับสินค้าและกดยืนยันรับสินค้าแล้ว') }}
                            @endif
                        </p>
                    </div>

                    @if ($order->isDelivered())
                        <div class="flex flex-col gap-6 divide-y divide-gulu-line/50">
                            @foreach ($order->items as $item)
                                @php
                                    $product = $item->product;
                                    $review = $product ? ($reviews->get($product->id) ?? null) : null;
                                @endphp

                                @if ($product)
                                    <div class="pt-6 first:pt-0 flex flex-col gap-4">
                                        <div class="flex items-center gap-3">
                                            <div class="size-12 shrink-0 overflow-hidden rounded-xl bg-gulu-lilac-soft flex items-center justify-center">
                                                @if ($product->image_url)
                                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="size-full object-cover">
                                                @else
                                                    <flux:icon.photo class="size-5 text-gulu-muted" />
                                                @endif
                                            </div>
                                            <div>
                                                <h3 class="text-base font-medium text-gulu-ink">{{ $product->name }}</h3>
                                                @if ($review)
                                                    <div class="flex items-center gap-2 mt-0.5">
                                                        <x-rating-stars :rating="$review->rating" />
                                                        <span class="text-xs text-gulu-success font-medium">{{ __('(รีวิวแล้ว)') }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Review Form --}}
                                        <form method="POST" action="{{ route('reviews.store', $product) }}"
                                              class="rounded-2xl bg-gulu-mist p-5 flex flex-col gap-4">
                                            @csrf

                                            <div>
                                                <label class="block text-xs font-medium uppercase tracking-wider text-gulu-muted mb-2">
                                                    {{ __('คะแนนความพึงพอใจ (1-5 ดาว)') }}
                                                </label>
                                                <x-rating-input name="rating" :value="old('rating', $review?->rating ?? 5)" />
                                            </div>

                                            <div>
                                                <label for="comment_{{ $product->id }}" class="block text-xs font-medium uppercase tracking-wider text-gulu-muted mb-2">
                                                    {{ __('ความคิดเห็น (ไม่บังคับ)') }}
                                                </label>
                                                <textarea id="comment_{{ $product->id }}" name="comment" rows="2"
                                                    placeholder="{{ __('บอกเล่าความรู้สึกเกี่ยวกับดอกไม้ช่อนี้...') }}"
                                                    class="w-full rounded-2xl border border-gulu-line bg-white px-4 py-3 text-sm outline-none transition placeholder:text-gulu-muted focus:border-gulu-ink focus:ring-2 focus:ring-gulu-lilac/40">{{ old('comment', $review?->comment) }}</textarea>
                                            </div>

                                            <div class="flex justify-end">
                                                <x-button type="submit" size="sm">
                                                    {{ $review ? __('อัปเดตรีวิว') : __('ส่งรีวิว') }}
                                                </x-button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-2xl bg-gulu-mist p-6 text-center text-sm text-gulu-muted">
                            <flux:icon.lock-closed class="mx-auto size-6 text-gulu-muted mb-2" />
                            {{ __('ระบบรีวิวจะปลดล็อกทันทีเมื่อลูกค้ากดปุ่ม "ฉันได้รับสินค้าแล้ว"') }}
                        </div>
                    @endif
                </section>
            </div>

            {{-- Right: Order Summary / Receipt Card --}}
            <div class="lg:col-span-5 lg:sticky lg:top-28">
                <div class="rounded-3xl bg-gulu-lilac-soft p-6 sm:p-8 flex flex-col gap-5">
                    <h2 class="font-display text-3xl text-gulu-ink">{{ __('ใบเสร็จสรุปยอด') }}</h2>

                    <div class="flex justify-between items-center text-base">
                        <span class="text-gulu-muted">{{ __('รวมราคาสินค้า') }}</span>
                        <span class="font-medium text-gulu-ink">฿{{ number_format((float) $order->total_amount, 2) }}</span>
                    </div>

                    <div class="flex justify-between items-center text-base">
                        <span class="text-gulu-muted">{{ __('ค่าจัดส่ง') }}</span>
                        <span class="font-medium text-gulu-ink">{{ __('Free') }}</span>
                    </div>

                    <div class="border-t border-gulu-line/70 pt-4 flex justify-between items-baseline">
                        <span class="text-base font-medium text-gulu-ink">{{ __('ยอดชำระเงินสุทธิ') }}</span>
                        <span class="font-display text-3xl font-semibold text-gulu-ink">
                            ฿{{ number_format((float) $order->total_amount, 2) }}
                        </span>
                    </div>

                    <div class="mt-4 flex items-center justify-between rounded-2xl bg-white/70 px-4 py-3 text-xs text-gulu-muted">
                        <span>{{ __('สถานะการชำระเงิน:') }}</span>
                        <span class="font-semibold text-gulu-ink">{{ __('ชำระเงินเรียบร้อยแล้ว') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::shop>

