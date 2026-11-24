<?php

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Masukkan alamat email.',
            'email.string' => 'Masukkan alamat email yang valid.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'email.max' => 'Alamat email maksimal 255 karakter.',
            'password.required' => 'Masukkan kata sandi.',
            'password.string' => 'Masukkan kata sandi yang valid.',
        ];
    }
}
