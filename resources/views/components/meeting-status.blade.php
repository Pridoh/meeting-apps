@props(['status'])

@php
    [$label, $variant] = match ($status) {
        'ongoing' => ['Berlangsung', 'info'],
        'completed' => ['Selesai', 'success'],
        default => ['Terjadwal', 'neutral'],
    };
@endphp

<x-status-badge :variant="$variant">{{ $label }}</x-status-badge>
