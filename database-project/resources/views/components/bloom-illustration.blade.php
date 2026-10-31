{{-- Decorative flowers for the auth pages (Figma: 02 Sign Up / 03 Sign In, left panel). --}}
@php
    $flower = function (float $cx, float $cy, float $petal, string $fill, float $opacity) {
        $petals = '';
        foreach ([0, 72, 144, 216, 288] as $deg) {
            $petals .= sprintf(
                '<ellipse cx="%1$s" cy="%2$s" rx="%3$s" ry="%4$s" transform="rotate(%5$s %1$s %6$s)" fill="%7$s" fill-opacity="%8$s"/>',
                $cx, $cy - $petal * 0.55, $petal * 0.42, $petal * 0.62, $deg, $cy, $fill, $opacity,
            );
        }

        return $petals.sprintf('<circle cx="%s" cy="%s" r="%s" fill="#e8b84a" fill-opacity="0.9"/>', $cx, $cy, $petal * 0.2);
    };
@endphp

<svg viewBox="0 0 400 400" {{ $attributes->merge(['class' => 'h-auto w-full', 'aria-hidden' => 'true']) }}>
    <circle cx="205" cy="200" r="160" fill="#ffffff" fill-opacity="0.18" stroke="#ffffff" stroke-opacity="0.6" stroke-width="1.5"/>
    {!! $flower(195, 170, 115, '#8e6bbf', 0.8) !!}
    {!! $flower(318, 250, 80, '#b79ad6', 0.85) !!}
    {!! $flower(95, 300, 72, '#6f4fa3', 0.85) !!}
</svg>
