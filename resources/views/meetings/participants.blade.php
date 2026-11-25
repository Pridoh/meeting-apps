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
        <p class="empty-state mt-3">Belum ada peserta.</p>
    @endforelse

    <form method="POST" action="{{ route('meetings.participants.store', $meeting) }}" class="mt-5 grid gap-3">
        @csrf
        <x-input id="peserta-email" name="email" label="Email penerima" type="email" :value="old('email')" autocomplete="off" maxlength="255" required />
        <div><x-button>Kirim undangan</x-button></div>
    </form>
    @foreach ($invitations as $invitation)
        @php
            [$label, $variant] = match ($invitation->status) {
                'pending' => ['Menunggu respons', 'warning'],
                'accepted' => ['Diterima', 'success'],
                'declined' => ['Ditolak', 'neutral'],
                'revoked' => ['Dicabut', 'neutral'],
                'expired' => ['Kedaluwarsa', 'neutral'],
                default => [$invitation->status, 'neutral'],
            };
        @endphp
        <article class="mt-4 grid gap-3 rounded-xl border border-line bg-cream/35 p-3">
            <div class="flex min-w-0 flex-wrap items-center gap-2">
                <strong class="wrap-anywhere">{{ $invitation->email }}</strong>
                <x-status-badge :variant="$variant">{{ $label }}</x-status-badge>
            </div>
            @if ($invitation->responded_at)
                <p class="text-sm text-muted">Respons pada
                    <time datetime="{{ $invitation->responded_at->toIso8601String() }}">{{ $invitation->responded_at->timezone($meeting->timezone)->locale('id')->translatedFormat('d M Y, H.i T') }}</time>
                </p>
            @endif
            @if ($invitation->deliveries->isNotEmpty())
                <ul class="grid gap-1 text-sm text-muted">
                    @foreach ($invitation->deliveries as $delivery)
                        <li class="wrap-anywhere">Pengiriman: {{ $delivery->status }} {{ $delivery->error_message }}</li>
                    @endforeach
                </ul>
            @endif
            @if ($invitation->status === 'pending')
                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('invitations.resend',[$meeting,$invitation->id]) }}">@csrf <x-button variant="secondary">Kirim ulang</x-button></form>
                    <form method="POST" action="{{ route('invitations.revoke',[$meeting,$invitation->id]) }}" data-confirm-with="cabut-{{ $invitation->id }}">@csrf @method('DELETE') <x-button variant="secondary">Cabut</x-button></form>
                    <x-confirmation-modal :id="'cabut-'.$invitation->id" title="Cabut undangan?" confirm-label="Cabut">Tautan undangan ini akan berhenti berlaku.</x-confirmation-modal>
                </div>
            @endif
        </article>
    @endforeach
</x-card>
