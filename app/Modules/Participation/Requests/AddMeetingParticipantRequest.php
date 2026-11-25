<?php

namespace App\Modules\Participation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddMeetingParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('meeting'));
    }

    /** @return array{email: list<string>} */
    public function rules(): array
    {
        return ['email' => ['required', 'string', 'email', 'max:255']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['email.*' => 'Masukkan alamat email penerima yang valid.'];
    }
}
