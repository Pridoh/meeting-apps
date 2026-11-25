@php($key = 'building-'.($editing?->id ?? 'new'))
<form method="POST" action="{{ $editing ? route('organizations.buildings.update', [$organization, $editing->id]) : route('organizations.buildings.store', $organization) }}" class="mt-3 grid gap-3">
    @csrf
    @if ($editing) @method('PATCH') @endif
    <x-input :id="$key.'-name'" name="name" label="Nama gedung" :value="$editing?->name" :show-error="false" required maxlength="120" />
    <x-input :id="$key.'-location'" name="location" label="Lokasi gedung" :value="$editing?->location" :show-error="false" maxlength="160" />
    @if ($editing)
        <x-form-select :id="$key.'-status'" name="is_active" label="Status gedung">
            <option value="1" @selected($editing->is_active)>Aktif</option>
            <option value="0" @selected(! $editing->is_active)>Nonaktif</option>
        </x-form-select>
    @endif
    <div><x-button variant="secondary">Simpan gedung</x-button></div>
</form>
