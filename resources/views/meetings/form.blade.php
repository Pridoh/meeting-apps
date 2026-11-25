@php
    $timezone = old('timezone', $meeting?->timezone ?? 'Asia/Jakarta');
    $mode = old('mode', $meeting?->mode ?? 'offline');
    $description = old('description', $meeting?->description);
@endphp

<x-input id="meeting-title" name="title" label="Judul meeting" :value="old('title', $meeting?->title)" maxlength="255" required />

<fieldset class="min-w-0 rounded-xl border border-line bg-cream/35 p-4">
    <legend class="px-2 text-sm font-semibold text-terracotta">Jadwal dan mode</legend>
    <div class="grid gap-5 sm:grid-cols-2">
        <x-input id="meeting-start" name="start_time" label="Waktu mulai" type="datetime-local" :value="old('start_time', $meeting?->start_time->timezone($meeting->timezone)->format('Y-m-d\TH:i:s'))" step="1" required />
        <x-input id="meeting-end" name="end_time" label="Waktu selesai" type="datetime-local" :value="old('end_time', $meeting?->end_time->timezone($meeting->timezone)->format('Y-m-d\TH:i:s'))" step="1" required />
        <x-form-select id="meeting-timezone" name="timezone" label="Zona waktu" :error="$errors->first('timezone')" required>
            @foreach (DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC) as $zone)
                <option value="{{ $zone }}" @selected($zone === $timezone)>{{ $zone }}</option>
            @endforeach
        </x-form-select>

        <x-form-select id="meeting-mode" name="mode" label="Mode" :error="$errors->first('mode')" required>
            <option value="offline" @selected($mode === 'offline')>Offline</option>
            <option value="online" @selected($mode === 'online')>Online</option>
        </x-form-select>
    </div>
</fieldset>

<x-input id="meeting-location" name="location" label="Lokasi atau tautan meeting" :value="old('location', $meeting?->location)" maxlength="255" />
<x-form-textarea id="meeting-description" name="description" label="Deskripsi" :error="$errors->first('description')" rows="4" maxlength="10000">{{ is_string($description) ? $description : '' }}</x-form-textarea>

<div class="flex flex-wrap gap-3 border-t border-line pt-5">
    <x-button>{{ $meeting ? 'Simpan perubahan' : 'Buat meeting' }}</x-button>
    <a class="button button--secondary" href="{{ $cancelUrl }}">Batal</a>
</div>
