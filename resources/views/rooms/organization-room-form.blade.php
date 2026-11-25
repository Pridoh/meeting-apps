@php($key = 'catalog-room-'.($editing?->id ?? 'new'))
<form method="POST" action="{{ $editing ? route('organizations.rooms.update', [$organization, $editing]) : route('organizations.rooms.store', $organization) }}" class="mt-3 grid gap-3">
    @csrf
    @if ($editing) @method('PATCH') @endif
    <x-form-select :id="$key.'-building'" name="building_id" label="Gedung" required>
        @foreach ($buildings as $option)
            <option value="{{ $option->id }}" @selected($editing?->building_id === $option->id)>{{ $option->name }}</option>
        @endforeach
    </x-form-select>
    <x-input :id="$key.'-name'" name="name" label="Nama ruang" :value="$editing?->name" :show-error="false" required maxlength="120" />
    <x-input :id="$key.'-location'" name="location" label="Rincian lokasi" :value="$editing?->location" :show-error="false" maxlength="160" />
    <x-input :id="$key.'-capacity'" name="capacity" type="number" label="Kapasitas kursi" :value="$editing?->capacity" :show-error="false" required min="1" />
    @if ($editing)
        <x-form-select :id="$key.'-status'" name="is_active" label="Status ruang">
            <option value="1" @selected($editing->is_active)>Aktif</option>
            <option value="0" @selected(! $editing->is_active)>Nonaktif</option>
        </x-form-select>
    @endif
    <div><x-button variant="secondary">Simpan ruang</x-button></div>
</form>
