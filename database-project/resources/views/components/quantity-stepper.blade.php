{{--
    Quantity +/- (Alpine, ships with Flux). Submits the number as "quantity":
    <x-quantity-stepper name="quantity" :value="1" :max="$product->stock" />
--}}
@props([
    'name' => 'quantity',
    'value' => 1,
    'min' => 1,
    'max' => 99,
])

<div x-data="{ qty: {{ (int) $value }} }" {{ $attributes->class('inline-flex items-center gap-5 rounded-full border border-gulu-line px-5 py-2.5') }}>
    <button type="button" x-on:click="qty = Math.max({{ (int) $min }}, qty - 1)" class="text-xl leading-none" aria-label="{{ __('Decrease') }}">−</button>
    <input type="number" name="{{ $name }}" x-model.number="qty" min="{{ $min }}" max="{{ $max }}" class="w-10 appearance-none border-0 bg-transparent p-0 text-center text-lg font-medium outline-none" readonly>
    <button type="button" x-on:click="qty = Math.min({{ (int) $max }}, qty + 1)" class="text-xl leading-none" aria-label="{{ __('Increase') }}">+</button>
</div>
