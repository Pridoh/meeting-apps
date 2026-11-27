<x-auth.layout
    title="Masuk ke akun"
    subtitle="Lanjutkan rapat yang sedang berjalan, tinjau notulen, dan periksa tindak lanjut tim."
>
    <form class="auth-form" method="POST" action="#">
        @csrf

        <label class="field">
            <span>Email</span>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email" required autofocus>
            @error('email')
                <em class="field-error">{{ $message }}</em>
            @enderror
        </label>

        <label class="field">
            <span>Password</span>
            <span class="field-control">
                <input type="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
                <button class="field-toggle" type="button" data-password-toggle aria-pressed="false" aria-label="Tampilkan password">
                    <x-icon name="eye" size="17" class="eye-show" />
                    <x-icon name="eye-off" size="17" class="eye-hide" />
                </button>
            </span>
            @error('password')
                <em class="field-error">{{ $message }}</em>
            @enderror
        </label>

        <div class="auth-row">
            <label class="check">
                <input type="checkbox" name="remember">
                Ingat saya
            </label>
        </div>

        <button class="auth-submit" type="submit">Masuk</button>
    </form>

    <p class="auth-alt">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
</x-auth.layout>
