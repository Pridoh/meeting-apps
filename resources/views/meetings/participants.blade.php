<x-card aria-labelledby="peserta-heading">
    <h2 id="peserta-heading" class="section-title">Peserta</h2>
    @error('confirmed')
        <p role="alert" class="form-error">{{ $message }}</p>
    @enderror
    @forelse ($meeting->participants as $participant)
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line py-3">
            <div class="min-w-0">
                <p class="wrap-anywhere font-semibold">{{ $participant->name }}</p>
                <p class="wrap-anywhere text-sm text-muted">{{ $participant->email }}</p>
            </div>
            @php
                $modalId = 'hapus-peserta-'.$participant->id;
            @endphp
            <form method="POST" action="{{ route('meetings.participants.destroy', [$meeting, $participant]) }}" data-confirm-with="{{ $modalId }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="confirmed" value="">
                <x-button variant="secondary" aria-haspopup="dialog" :aria-label="'Hapus peserta '.$participant->name">Hapus</x-button>
            </form>
            <x-confirmation-modal :id="$modalId" title="Hapus peserta?" confirm-label="Hapus peserta">
                {{ $participant->name }} akan kehilangan akses meeting. Tugas aktifnya akan kehilangan penanggung jawab. Notulen final tetap tersimpan.
            </x-confirmation-modal>
        </div>
    @empty
        <p class="mt-3 text-muted">Belum ada peserta.</p>
    @endforelse

    <form method="POST" action="{{ route('meetings.participants.store', $meeting) }}" class="mt-5 grid gap-3">
        @csrf
        <x-input id="peserta-email" name="email" label="Email akun terdaftar" type="email" :value="old('email')" maxlength="255" required />
        <div><x-button>Tambah peserta</x-button></div>
    </form>
</x-card>
