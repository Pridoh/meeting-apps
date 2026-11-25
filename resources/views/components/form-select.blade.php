@props(['id', 'label', 'name', 'error' => null])

<div>
    <label class="field-label" for="{{ $id }}">{{ $label }}</label>
    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->class(['form-input']) }}
    >
        {{ $slot }}
    </select>
    @if ($error)
        <p id="{{ $id }}-error" class="form-error">{{ $error }}</p>
    @endif
</div>
