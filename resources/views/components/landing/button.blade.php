@props(['href'])

<a href="{{ $href }}" {{ $attributes->class('btn') }}>
    {{ $slot }}
    <span><x-icon name="arrow-right" size="16" /></span>
</a>
