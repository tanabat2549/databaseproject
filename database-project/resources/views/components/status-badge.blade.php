{{-- <x-status-badge :status="$order->status" />   statuses: pending | delivered --}}
@props(['status' => 'pending'])

@php
    $isDelivered = $status === 'delivered';
@endphp

<span {{ $attributes->class([
    'inline-flex items-center rounded-full px-3.5 py-1.5 text-[13px] font-medium',
    'bg-gulu-success/15 text-gulu-success' => $isDelivered,
    'bg-gulu-gold/20 text-gulu-ink' => ! $isDelivered,
]) }}>
    {{ $isDelivered ? __('delivered') : __('pending') }}
</span>
