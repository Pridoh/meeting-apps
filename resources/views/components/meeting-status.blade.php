@props(['status'])

@php
    [$label, $variant] = match ($status) {
        'ongoing' => ['Berlangsung', 'info'],
        'completed' => ['Selesai', 'success'],
        default => ['Terjadwal', 'neutral'],
    };
@endphp

<x-status-badge :variant="$variant" {{ $attributes }}>{{ $label }}</x-status-badge>
