@props(['name', 'size' => 24])

<svg {{ $attributes->merge([
    'width' => $size,
    'height' => $size,
    'viewBox' => '0 0 24 24',
    'fill' => 'none',
    'stroke' => 'currentColor',
    'stroke-width' => 2,
    'stroke-linecap' => 'round',
    'stroke-linejoin' => 'round',
    'aria-hidden' => 'true',
    'focusable' => 'false',
]) }}>
    <use href="{{ asset('icons/feather/feather-sprite.svg') }}#{{ $name }}" />
</svg>
