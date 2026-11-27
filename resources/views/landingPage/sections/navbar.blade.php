<header class="hero-nav">
    <x-landing.brand />
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="landing-menu" aria-label="Buka menu navigasi">
        <x-icon name="menu" size="22" />
        <x-icon name="x" size="22" />
    </button>
    <nav id="landing-menu" aria-label="Navigasi utama">
        <a href="#fitur">Fitur</a>
        <a href="#alur">Alur rapat</a>
        <a href="#ruang">Ruang</a>
        <div class="hero-nav-auth">
            <a href="{{ route('login') }}">Masuk</a>
            <a class="hero-nav-register" href="{{ route('register') }}">Daftar</a>
        </div>
    </nav>
    <x-theme-toggle />
</header>
