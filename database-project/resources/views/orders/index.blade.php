<x-layouts::shop :title="__('Your orders')">
    <div class="flex flex-col gap-6">
        <div>
            <x-button variant="outline" size="sm" :href="route('home')" class="w-fit">
                <flux:icon.arrow-left class="size-4" />{{ __('Back') }}
            </x-button>
        </div>

        <div>
            <h1 class="font-display text-4xl sm:text-5xl font-medium tracking-tight text-gulu-ink">{{ __('Your orders') }}</h1>
            <p class="mt-2 text-base text-gulu-muted">{{ __('Track and review what you have ordered') }}</p>
        </div>

        @if ($orders->isEmpty())
            <div class="mt-4 flex flex-col items-center justify-center rounded-3xl border border-dashed border-gulu-line p-12 text-center bg-white">
                <div class="flex size-16 items-center justify-center rounded-full bg-gulu-lilac-soft text-gulu-lilac-deep">
                    <flux:icon.archive-box class="size-8" />
                </div>
                <h2 class="mt-4 font-display text-2xl text-gulu-ink">{{ __('ยังไม่มีประวัติการสั่งซื้อ') }}</h2>
                <p class="mt-1 text-sm text-gulu-muted">{{ __('คุณยังไม่เคยทำการสั่งซื้อดอกไม้ เริ่มต้นเลือกดูช่อดอกไม้สวยๆ ได้เลย') }}</p>
                <x-button class="mt-6" :href="route('products.index')">
                    {{ __('เลือกซื้อดอกไม้') }}
                </x-button>
            </div>
        @else
            <div class="mt-4 flex flex-col gap-5">
                @foreach ($orders as $order)
                    <article class="flex flex-col gap-5 rounded-3xl border border-gulu-line bg-white p-6 sm:p-7 transition hover:border-gulu-lilac">
                        {{-- Top row: Order ID, status badge, delivery date, total price --}}
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <a href="{{ route('orders.show', $order) }}" class="text-base sm:text-lg font-semibold text-gulu-ink hover:underline">
                                    {{ __('Order #:id', ['id' => $order->id]) }}
                                </a>
                                <x-status-badge :status="$order->status" />
                                <span class="text-xs sm:text-sm text-gulu-muted">
                                    @if ($order->isDelivered())
                                        {{ __('delivered :date', ['date' => $order->delivery_date->format('j M Y')]) }}
                                    @else
                                        {{ __('arriving :date', ['date' => $order->delivery_date->format('j M Y')]) }}
                                    @endif
                                </span>
                            </div>

                            <div class="text-right">
                                <span class="font-display text-2xl sm:text-3xl font-semibold text-gulu-ink">
                                    ฿{{ number_format((float) $order->total_amount) }}
                                </span>
                            </div>
                        </div>

                        {{-- Bottom row: Product thumbnails, title summary, item count, action button --}}
                        <div class="flex flex-col gap-4 border-t border-gulu-line/50 pt-4 sm:flex-row sm:items-center sm:justify-between">
                            <a href="{{ route('orders.show', $order) }}" class="group flex items-center gap-4 min-w-0">
                                {{-- Product Thumbnails --}}
                                <div class="flex items-center -space-x-2 shrink-0">
                                    @foreach ($order->items->take(3) as $item)
                                        <div class="size-12 sm:size-14 overflow-hidden rounded-2xl border-2 border-white bg-gulu-lilac-soft shadow-xs flex items-center justify-center">
                                            @if ($item->product?->image_url)
                                                <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" loading="lazy" class="size-full object-cover">
                                            @else
                                                <flux:icon.photo class="size-6 text-gulu-muted" />
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Text summary & count --}}
                                <div class="min-w-0">
                                    <p class="truncate text-base font-medium text-gulu-ink group-hover:underline">
                                        {{ $order->items->map(fn($item) => $item->product?->name)->filter()->join(' · ') }}
                                    </p>
                                    <p class="text-xs sm:text-sm text-gulu-muted mt-0.5">
                                        {{ trans_choice(':count item|:count items', $order->items_count ?? $order->items->count()) }}
                                    </p>
                                </div>
                            </a>

                            {{-- Actions --}}
                            <div class="flex items-center justify-end gap-3 shrink-0">
                                <a href="{{ route('orders.show', $order) }}" class="text-sm font-medium text-gulu-muted hover:text-gulu-ink underline mr-2">
                                    {{ __('รายละเอียด') }}
                                </a>

                                @if ($order->isPending())
                                    <form method="POST" action="{{ route('orders.receive', $order) }}"
                                          onsubmit="return confirm('{{ __('ยืนยันว่าคุณได้รับสินค้าสำหรับคำสั่งซื้อ #:id เรียบร้อยแล้ว?', ['id' => $order->id]) }}')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="inline-flex items-center justify-center rounded-full border border-gulu-ink bg-white px-5 py-2.5 text-sm font-medium text-gulu-ink transition hover:bg-gulu-ink hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-gulu-lilac-deep">
                                            {{ __('ฉันได้รับสินค้าแล้ว') }}
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('orders.show', $order) }}"
                                        class="inline-flex items-center justify-center rounded-full bg-gulu-ink px-7 py-2.5 text-sm font-medium text-white transition hover:bg-gulu-plum focus:outline-none focus-visible:ring-2 focus-visible:ring-gulu-lilac-deep">
                                        {{ __('review') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts::shop>

