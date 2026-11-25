<?php

namespace App\Modules\Auth\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RegistrationController
{
    public function store(Request $request): RedirectResponse
    {
        if (is_string($request->email)) {
            $request->merge(['email' => Str::lower(trim($request->email))]);
        }
        $data = $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users', 'password' => 'required|string|min:8|max:72|confirmed']);
        $user = User::create($data);
        event(new Registered($user));
        Auth::login($user);

        return to_route('verification.notice');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->intended($request->session()->has('invite_hash') ? route('invitation.show') : route('dashboard'));
    }

    public function resend(Request $request): RedirectResponse
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->user()->sendEmailVerificationNotification();
        }

        return back()->with('success', 'Periksa kotak masuk Anda.');
    }

    public function switchAccount(Request $request): RedirectResponse
    {
        $hash = $request->session()->get('invite_hash');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put(['invite_hash' => $hash, 'url.intended' => route('invitation.show')]);

        return to_route('login');
    }
}
