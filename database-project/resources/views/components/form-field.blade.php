{{--
    <x-form-field name="email" type="email" :label="__('Email')" placeholder="you@example.com" />
    Fills its value from old(); shows the validation error for $name.
--}}
@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
])

<div class="flex flex-col gap-2">
    @if ($label)
        <label for="{{ $name }}" class="text-[13px] font-medium uppercase tracking-wider text-gulu-muted">{{ $label }}</label>
    @endif

    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
           @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
           {{ $attributes->class([
               'w-full rounded-2xl border bg-white px-5 py-4 text-base outline-none transition placeholder:text-gulu-muted focus:border-gulu-ink focus:ring-2 focus:ring-gulu-lilac/40',
               'border-gulu-line' => ! $errors->has($name),
               'border-red-400' => $errors->has($name),
           ]) }}>

    @error($name)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
