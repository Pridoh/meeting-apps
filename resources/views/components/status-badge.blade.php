@props(['variant' => 'neutral'])

<span {{ $attributes->class(['status-badge', "status-badge--{$variant}"]) }}>
    {{ $slot }}
</span>
