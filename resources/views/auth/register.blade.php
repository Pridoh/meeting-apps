<x-auth.layout
    title="Buat akun"
    subtitle="Mulai dari satu rapat, lalu simpan agenda, keputusan, dan tugasnya di satu tempat."
>
    <form class="auth-form" method="POST" action="#">
        @csrf

        <label class="field">
            <span>Nama lengkap</span>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama kamu" autocomplete="name" required autofocus>
            @error('name')
                <em class="field-error">{{ $message }}</em>
            @enderror
        </label>

        <label class="field">
            <span>Email</span>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email" required>
            @error('email')
                <em class="field-error">{{ $message }}</em>
            @enderror
        </label>

        <label class="field">
            <span>Password</span>
            <span class="field-control">
                <input type="password" name="password" placeholder="Minimal 8 karakter" autocomplete="new-password" required>
                <button class="field-toggle" type="button" data-password-toggle aria-pressed="false" aria-label="Tampilkan kata sandi">
                    <x-icon name="eye" size="17" class="eye-show" />
                    <x-icon name="eye-off" size="17" class="eye-hide" />
                </button>
            </span>
            @error('password')
                <em class="field-error">{{ $message }}</em>
            @enderror
        </label>

        <label class="field">
            <span>Ulangi Password</span>
            <span class="field-control">
                <input type="password" name="password_confirmation" placeholder="Ulangi password" autocomplete="new-password" required>
                <button class="field-toggle" type="button" data-password-toggle aria-pressed="false" aria-label="Tampilkan kata sandi">
                    <x-icon name="eye" size="17" class="eye-show" />
                    <x-icon name="eye-off" size="17" class="eye-hide" />
                </button>
            </span>
            @error('password')
                <em class="field-error">{{ $message }}</em>
            @enderror
        </label>

        <button class="auth-submit" type="submit">Buat akun</button>
        <p class="auth-note">Dengan mendaftar, kamu menyetujui penggunaan data hanya untuk keperluan operasional rapat.</p>
    </form>

    <p class="auth-alt">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
</x-auth.layout>
