<x-card aria-labelledby="agenda-heading">
    <h2 id="agenda-heading" class="section-title">Agenda</h2>
    @if ($meeting->agendas->isEmpty())
        <p class="mt-3 text-muted">Belum ada agenda.</p>
    @else
        <ol class="mt-2 divide-y divide-line">
            @foreach ($meeting->agendas as $agenda)
                @php
                    $id = 'agenda-'.$agenda->id;
                @endphp
                <li class="grid min-w-0 grid-cols-[2rem_minmax(0,1fr)] gap-x-3 py-4">
                    <span class="text-lg tabular-nums text-terracotta" aria-hidden="true">{{ $loop->iteration }}.</span>
                    <div>
                        <h3 class="wrap-anywhere font-semibold">{{ $agenda->title }}</h3>
                        @if ($agenda->description)
                            <p class="mt-1 wrap-anywhere whitespace-pre-line text-sm text-muted">{{ $agenda->description }}</p>
                        @endif
                        @if ($isOrganizer)
                            <details class="mt-2" @if (old('_agenda') === $id) open @endif>
                                <summary class="min-h-11 cursor-pointer py-2 font-semibold text-terracotta underline decoration-line underline-offset-4 hover:decoration-current">Ubah agenda</summary>
                                <form method="POST" action="{{ route('meetings.agendas.update', [$meeting, $agenda]) }}" class="grid gap-3">
                                    @csrf
                                    @method('PATCH')
                                    @include('meetings.agenda-fields', ['agenda' => $agenda])
                                    <div><x-button variant="secondary">Simpan agenda</x-button></div>
                                </form>
                                <form method="POST" action="{{ route('meetings.agendas.destroy', [$meeting, $agenda]) }}" data-confirm-with="{{ $id }}-hapus" class="mt-3">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="secondary" aria-haspopup="dialog">Hapus agenda</x-button>
                                </form>
                                <x-confirmation-modal :id="$id.'-hapus'" title="Hapus agenda?" confirm-label="Hapus agenda">
                                    Agenda “{{ $agenda->title }}” akan dihapus dari meeting ini.
                                </x-confirmation-modal>
                            </details>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endif

    @if ($isOrganizer)
        @if ($meeting->agendas->count() > 1)
            <details class="border-t border-line pt-3" @if ($errors->has('agenda_ids') || $errors->has('agenda_ids.*')) open @endif>
                <summary class="min-h-11 cursor-pointer py-2 font-semibold text-terracotta">Atur urutan agenda</summary>
                <form method="POST" action="{{ route('meetings.agendas.reorder', $meeting) }}" class="grid gap-3 pb-4">
                    @csrf
                    @method('PATCH')
                    <p class="text-sm text-muted">Pilih satu agenda berbeda untuk setiap posisi.</p>
                    @error('agenda_ids')
                        <p role="alert" class="form-error">{{ $message }}</p>
                    @enderror
                    @foreach ($meeting->agendas as $agenda)
                        @php
                            $position = $loop->index;
                            $selectedId = old('agenda_ids.'.$position, $agenda->id);
                        @endphp
                        <x-form-select :id="'agenda-posisi-'.$position" name="agenda_ids[]" :label="'Posisi '.$loop->iteration" :error="$errors->first('agenda_ids.'.$position)" required>
                            @foreach ($meeting->agendas as $option)
                                <option value="{{ $option->id }}" @selected(is_scalar($selectedId) && (string) $option->id === (string) $selectedId)>{{ $option->title }}</option>
                            @endforeach
                        </x-form-select>
                    @endforeach
                    <div><x-button variant="secondary">Simpan urutan</x-button></div>
                </form>
            </details>
        @endif
        <details class="mt-3 border-t border-line pt-3" @if (old('_agenda') === 'agenda-baru') open @endif>
            <summary class="min-h-11 cursor-pointer py-2 font-semibold text-terracotta">Tambah agenda</summary>
            <form method="POST" action="{{ route('meetings.agendas.store', $meeting) }}" class="grid gap-3">
                @csrf
                @include('meetings.agenda-fields')
                <div><x-button>Tambahkan agenda</x-button></div>
            </form>
        </details>
    @endif
</x-card>
