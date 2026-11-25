@extends('layouts.guest')

@section('title', 'Undangan meeting')

@section('content')
    <h1 class="page-title">Undangan meeting</h1>
    <form hidden method="POST" action="{{ route('invitation.open') }}" id="open-invite">
        @csrf
        <input type="hidden" name="token">
    </form>

    @if (! $invite)
        <p class="intro-text">Jika undangan tidak terbuka, minta Organizer mengirim tautan baru.</p>
    @else
        @php
            $meeting = $invite->meeting;
            $start = $meeting->start_time->timezone($meeting->timezone);
            $end = $meeting->end_time->timezone($meeting->timezone);
        @endphp
        <section class="mt-6 rounded-xl border border-line bg-cream/55 p-4" aria-labelledby="invitation-meeting-title">
            <h2 id="invitation-meeting-title" class="section-title wrap-anywhere">{{ $meeting->title }}</h2>
            <p class="mt-2 wrap-anywhere text-sm text-muted">Organizer: {{ $meeting->organizer->name }}</p>
            <p class="mt-3 text-sm font-semibold tabular-nums">
                <time datetime="{{ $start->toIso8601String() }}">{{ $start->locale('id')->translatedFormat('d M Y, H.i') }}</time>
                <span aria-hidden="true">–</span><span class="sr-only"> sampai </span>
                <time datetime="{{ $end->toIso8601String() }}">{{ $end->locale('id')->translatedFormat($start->isSameDay($end) ? 'H.i' : 'd M Y, H.i') }}</time>
                <span class="block font-normal text-muted">{{ $meeting->timezone }}</span>
            </p>
            <p class="mt-2 wrap-anywhere text-sm text-muted">{{ $meeting->location }} · {{ $meeting->room_status }}</p>
        </section>

        @guest
            <div class="mt-6 flex flex-wrap gap-3">
                <a class="button button--primary" href="{{ route('login') }}">Masuk</a>
                <a class="button button--secondary" href="{{ route('register') }}">Daftar</a>
            </div>
        @else
            @if (strtolower(auth()->user()->email) !== $invite->email)
                <p class="intro-text">Gunakan akun yang menerima email undangan.</p>
                <form method="POST" action="{{ route('invitation.switch') }}" class="mt-6">
                    @csrf
                    <x-button>Ganti akun</x-button>
                </form>
            @elseif (! auth()->user()->hasVerifiedEmail())
                <a class="button button--primary mt-6" href="{{ route('verification.notice') }}">Verifikasi email</a>
            @elseif ($invite->status === 'pending')
                <div class="mt-6 flex flex-wrap gap-3">
                    @foreach (['accepted' => 'Terima', 'declined' => 'Tolak'] as $answer => $label)
                        <form method="POST" action="{{ route('invitation.respond', $answer) }}">
                            @csrf
                            <input type="hidden" name="invitation_id" value="{{ $invite->id }}">
                            <x-button :variant="$answer === 'accepted' ? 'primary' : 'secondary'">{{ $label }}</x-button>
                        </form>
                    @endforeach
                </div>
            @else
                <div class="mt-6 flex flex-wrap items-center gap-3">
                    @if ($invite->status === 'accepted')
                        <x-status-badge variant="success">Undangan diterima</x-status-badge>
                        <a class="button button--primary" href="{{ route('meetings.show', $meeting) }}">Buka meeting</a>
                    @else
                        <x-status-badge>Undangan ditolak</x-status-badge>
                    @endif
                </div>
            @endif
            @if ($hasConflict)
                <aside class="mt-5 rounded-lg border border-line bg-cream p-4">
                    <p class="font-semibold text-terracotta">Jadwal bertumpuk</p>
                    <p class="mt-1 text-sm text-muted">Tinjau jadwal lain dan pilih meeting yang akan Anda hadiri.</p>
                    <a class="button button--secondary mt-3" href="{{ route('attendance.index') }}">Lihat pilihan kehadiran</a>
                </aside>
            @endif
        @endguest
    @endif
@endsection

@section('page-scripts')
    <script>
        if (location.hash.length > 1) {
            const form = document.getElementById('open-invite');
            form.elements.token.value = location.hash.slice(1);
            history.replaceState(null, '', location.pathname);
            form.submit();
        }
    </script>
@endsection
