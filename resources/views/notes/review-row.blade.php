<div class="grid gap-2 rounded-md border border-line p-3" data-row>
    @if ($field === 'action_items')
        <label class="field-label">Tugas
            <textarea name="review[action_items][{{ $index }}][description]" class="form-input" rows="2" required>{{ $value['description'] ?? '' }}</textarea>
        </label>
        <label class="field-label">Penanggung jawab
            <select name="review[action_items][{{ $index }}][assignee]" class="form-input">
                <option value="">Belum ditugaskan</option>
                @foreach ($meeting->participants as $participant)
                    <option value="{{ $participant->email }}" @selected(($value['assignee'] ?? '') === $participant->email)>{{ $participant->name }} · {{ $participant->email }}</option>
                @endforeach
            </select>
        </label>
        <label class="field-label">Tenggat
            <input type="date" name="review[action_items][{{ $index }}][deadline]" value="{{ $value['deadline'] ?? '' }}" class="form-input">
        </label>
        <input type="hidden" name="review[action_items][{{ $index }}][status]" value="pending">
    @else
        <label class="field-label">{{ $heading }}
            <textarea name="review[{{ $field }}][{{ $index }}]" class="form-input" rows="2" required>{{ $value }}</textarea>
        </label>
    @endif
    <button type="button" class="justify-self-start text-sm text-terracotta underline" data-remove>Hapus {{ strtolower($heading) }}</button>
</div>
