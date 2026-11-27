@props(['title', 'subtitle'])

<!DOCTYPE html>
<html lang="id" class="landing-page">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} Rapatin</title>
    <x-theme-init />
    <link rel="icon" type="image/svg+xml" href="{{ asset('image/logo_rapatin.svg') }}">
    <x-rapatin-fonts />
    @vite(['resources/css/app.css', 'resources/js/auth.js'])
</head>
<body class="rapatin auth-page">
    <div class="auth-shell">
        <div class="auth-card">
            <aside class="auth-aside">
                <a class="brand" href="{{ route('home') }}" aria-label="Rapatin, kembali ke beranda">
                    <span class="brand-mark">
                        <img src="{{ asset('image/logo_rapatin.svg') }}" width="1200" height="800" alt="">
                    </span>
                    <span>Rapatin.</span>
                </a>

                <div class="auth-aside-copy">
                    <p class="eyebrow">You can easily access</p>
                    <h2>Smarter meetings, <span class="accent">Better outcomes</span></h2>
                    <p>Agenda, undangan, notulen, dan tindak lanjut dalam satu tempat.</p>
                    <div class="auth-aside-tags">
                        <span>Agenda</span>
                        <span>Notulen AI</span>
                        <span>Tindak lanjut</span>
                    </div>
                </div>
            </aside>

            <div class="auth-panel">
                <div class="auth-tools">
                    <a class="auth-back" href="{{ route('home') }}">
                        <x-icon name="arrow-left" size="14" /> Beranda
                    </a>
                    <x-theme-toggle />
                </div>

                <span class="auth-mark">
                    <img src="{{ asset('image/logo_rapatin.svg') }}" width="1200" height="800" alt="">
                </span>

                <h1>{{ $title }}</h1>
                <p class="auth-sub">{{ $subtitle }}</p>

                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
