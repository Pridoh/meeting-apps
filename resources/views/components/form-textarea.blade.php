@props(['id', 'label', 'name', 'error' => null])

<div class="min-w-0">
    <label class="field-label" for="{{ $id }}">{{ $label }}</label>
    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->class(['form-input']) }}
    >{{ $slot }}</textarea>
    @if ($error)
        <p id="{{ $id }}-error" class="form-error">{{ $error }}</p>
    @endif
</div>
