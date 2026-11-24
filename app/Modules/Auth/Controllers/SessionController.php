<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class SessionController
{
    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $credentials['email'] = strtolower($credentials['email']);
        $key = 'login:'.hash('sha256', $credentials['email'].'|'.$request->ip());

        // Dua request paralel untuk akun dan IP yang sama harus memeriksa batas secara bergiliran.
        $lock = Cache::lock($key.':attempt', 30);
        if (! $lock->get()) {
            throw ValidationException::withMessages(['email' => 'Percobaan masuk sedang diproses. Coba lagi sebentar.']);
        }

        try {
            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);
                throw ValidationException::withMessages(['email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik."]);
            }

            if (! Auth::attempt($credentials)) {
                RateLimiter::hit($key, 60);
                throw ValidationException::withMessages(['email' => 'Email atau kata sandi salah.']);
            }

            RateLimiter::clear($key);
        } finally {
            $lock->release();
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
