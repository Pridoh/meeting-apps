<?php

namespace App\Modules\Participation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RemoveMeetingParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('meeting'));
    }

    /** @return array{confirmed: list<string>} */
    public function rules(): array
    {
        return ['confirmed' => ['accepted']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['confirmed.accepted' => 'Konfirmasi dampak sebelum menghapus peserta.'];
    }
}
