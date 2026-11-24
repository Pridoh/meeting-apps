@props(['id', 'name', 'label', 'type' => 'text', 'value' => null])

<div>
    <label class="field-label" for="{{ $id }}">{{ $label }}</label>
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($value !== null) value="{{ $value }}" @endif
        @error($name) aria-invalid="true" aria-describedby="{{ $id }}-error" @enderror
        {{ $attributes->class(['form-input']) }}
    >
    @error($name)
        <p id="{{ $id }}-error" class="form-error">{{ $message }}</p>
    @enderror
</div>
