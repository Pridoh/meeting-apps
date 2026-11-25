@php
    $formId = isset($agenda) ? 'agenda-'.$agenda->id : 'agenda-baru';
    $submitted = old('_agenda') === $formId;
    $description = $submitted ? old('description') : ($agenda->description ?? null);
@endphp

<x-input :id="$formId.'-judul'" name="title" label="Judul agenda" :value="$submitted ? old('title') : ($agenda->title ?? null)" :show-error="$submitted" maxlength="255" required />
<x-form-textarea :id="$formId.'-deskripsi'" name="description" label="Deskripsi agenda" :error="$submitted ? $errors->first('description') : null" rows="2" maxlength="10000">{{ is_string($description) ? $description : '' }}</x-form-textarea>
<input type="hidden" name="_agenda" value="{{ $formId }}">
