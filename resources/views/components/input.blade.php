@props(['id', 'name', 'label', 'type' => 'text', 'value' => null, 'showError' => true])

<div class="min-w-0">
    <label class="field-label" for="{{ $id }}">{{ $label }}</label>
    @php($error = $showError ? $errors->first($name) : null)
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type === 'email') spellcheck="false" @endif
        @if (is_scalar($value)) value="{{ $value }}" @endif
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->class(['form-input']) }}
    >
    @if ($error)
        <p id="{{ $id }}-error" class="form-error">{{ $error }}</p>
    @endif
</div>
