@props(['name' => 'rating', 'value' => 5])

@php
    $captions = [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Very good', 5 => 'Excellent!'];
    $current = (int) $value;
@endphp

<div class="space-y-3">
    <div class="flex gap-2">
        @foreach (range(1, 5) as $n)
            <label class="cursor-pointer">
                <input type="radio" name="{{ $name }}" value="{{ $n }}" required
                       class="peer sr-only" @checked($current === $n)>
                <span class="flex size-12 items-center justify-center rounded-full border border-gulu-line bg-white text-lg transition hover:border-gulu-ink peer-checked:border-gulu-ink peer-checked:bg-gulu-ink peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-[#b794d0]">
                    {{ $n }}
                </span>
            </label>
        @endforeach
    </div>
    <p data-caption class="min-h-5 text-sm text-[#7a4fa3]">{{ $captions[$current] ?? '' }}</p>
</div>
<script>
    (function () {
        const root = document.currentScript.previousElementSibling;
        const captions = @json($captions);
        const caption = root.querySelector('[data-caption]');
        root.querySelectorAll('input[type=radio]').forEach((r) =>
            r.addEventListener('change', () => { caption.textContent = captions[r.value] || ''; })
        );
    })();
</script>