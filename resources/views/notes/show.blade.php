@extends('layouts.workspace')
@section('title', 'Notulen · '.$meeting->title)

@section('content')
<header class="workspace-heading">
    <a class="back-link" href="{{ route('meetings.show', $meeting) }}">← {{ $meeting->title }}</a>
    <h1 class="page-title mt-3">Notulen {{ $note?->is_finalized ? 'final' : 'rapat' }}</h1>
</header>
@php($pageErrors = collect($errors->getMessages())->except(['raw_draft', 'draft_file'])->flatten())
@if ($pageErrors->isNotEmpty())
    <div class="mb-4 rounded-md border border-danger p-4" role="alert">
        @foreach ($pageErrors as $error)<p>{{ $error }}</p>@endforeach
    </div>
@endif
<div class="grid gap-5">
@if ($note?->is_finalized)
    <x-card>
        <p class="text-sm text-muted">Difinalisasi <time datetime="{{ $note->finalized_at->toIso8601String() }}">{{ $note->finalized_at->timezone($meeting->timezone)->locale('id')->translatedFormat('d M Y, H.i T') }}</time></p>
        <h2 class="section-title mt-4">Ringkasan</h2>
        <p class="mt-2 wrap-anywhere whitespace-pre-line leading-relaxed">{{ $review['meeting_summary'] }}</p>
        @foreach (['key_discussion_points'=>'Poin diskusi','decisions'=>'Keputusan'] as $field=>$heading)
            <h2 class="section-title mt-5">{{ $heading }}</h2>
            <ul class="mt-2 list-disc space-y-2 wrap-anywhere pl-5">
                @forelse ($review[$field] as $line)<li>{{ $line }}</li>@empty<li>Belum ada.</li>@endforelse
            </ul>
        @endforeach
        <h2 class="section-title mt-5">Tindak lanjut</h2>
        <ul class="divide-y divide-line">
            @forelse ($review['action_items'] as $item)
                <li class="wrap-anywhere py-3"><strong>{{ $item['description'] }}</strong><br>{{ $item['assignee'] ?? 'Belum ditugaskan' }} · {{ $item['deadline'] ?? 'Tanpa tenggat' }}</li>
            @empty<li>Belum ada.</li>@endforelse
        </ul>
    </x-card>
@else
    <x-card>
        <form method="POST" enctype="multipart/form-data" action="{{ route('notes.draft',$meeting) }}" data-draft-form class="grid gap-3">
            @csrf @method('PUT')
            <input name="revision" type="hidden" value="{{ $note?->revision ?? 0 }}">
            <x-form-textarea id="raw_draft" name="raw_draft" label="Catatan mentah" :error="$errors->first('raw_draft')" rows="7">{{ is_string(old('raw_draft')) ? old('raw_draft') : $note?->raw_draft }}</x-form-textarea>
            <x-input id="draft_file" name="draft_file" label="Atau unggah teks (.txt, maksimal 1 MB)" type="file" accept=".txt,text/plain" class="text-sm" />
            @if ($review)<p class="text-sm text-muted">Mengubah draf menghapus hasil AI dan review sebelumnya.</p>@endif
            <div><x-button variant="secondary">Simpan draf</x-button></div>
        </form>
        @if ($note)
            <form method="POST" action="{{ route('notes.generate',$meeting) }}" class="mt-5 border-t border-line pt-4" data-ai-form>
                @csrf <input name="revision" type="hidden" value="{{ $note->revision }}">
                @if (config('services.notes.provider') === 'fake')<p class="mb-2 text-sm text-muted">Mode demo memakai contoh tetap. Periksa isinya terhadap draf asli.</p>@endif
                @if ($note->reviewed_data)<p class="mb-2 text-sm text-muted">Hasil baru akan menggantikan review tersimpan jika berhasil.</p>@endif
                <x-button>Proses draf dengan AI</x-button>
                <span role="status" class="text-sm text-muted"></span>
            </form>
        @endif
    </x-card>
    @if ($review)
        <x-card>
            <h2 class="section-title">Review hasil AI</h2>
            @if ($unknownAssignee)<p role="status" class="mt-2">Penanggung jawab yang tidak cocok dengan anggota dikosongkan. Periksa kembali tugas terkait.</p>@endif
            <form action="{{ route('notes.review',$meeting) }}" method="POST" class="mt-4 grid gap-5" data-review-form>
                @csrf @method('PUT')
                <input name="revision" type="hidden" value="{{ $note->revision }}">
                <label class="field-label">Ringkasan
                    <textarea class="form-input" rows="4" name="review[meeting_summary]" required>{{ $review['meeting_summary'] }}</textarea>
                </label>
                @foreach (['key_discussion_points'=>'Poin diskusi','decisions'=>'Keputusan','action_items'=>'Tindak lanjut'] as $field=>$heading)
                    <fieldset class="grid gap-3" data-list="{{ $field }}">
                        <legend class="field-label">{{ $heading }}</legend>
                        <div data-rows class="grid gap-2">
                            @foreach ($review[$field] as $index=>$value)
                                @include('notes.review-row')
                            @endforeach
                        </div>
                        <template>@include('notes.review-row', ['index'=>0,'value'=>null])</template>
                        <div><x-button type="button" variant="secondary" data-add>Tambah {{ strtolower($heading) }}</x-button></div>
                    </fieldset>
                @endforeach
                <div><x-button>Simpan review</x-button></div>
            </form>
        </x-card>
        @if ($note->reviewed_data)
            <x-card><form method="POST" action="{{ route('notes.finalize',$meeting) }}" data-finalize-form class="grid gap-4">
                @csrf <input name="revision" type="hidden" value="{{ $note->revision }}">
                <label class="flex min-h-11 items-start gap-3"><input class="mt-1 size-4 shrink-0 accent-terracotta" type="checkbox" required name="confirm" value="1"> Saya sudah memeriksa review tersimpan dan siap mengunci notulen ini.</label>
                <div><x-button>Finalisasi notulen</x-button> <span role="status" class="text-sm text-muted"></span></div>
            </form></x-card>
        @endif
    @endif
@endif
</div>
@endsection
