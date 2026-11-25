<?php

namespace App\Modules\Meetings\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderAgendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('meeting'));
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'agenda_ids' => ['required', 'array', 'list'],
            'agenda_ids.*' => ['required', 'integer', 'distinct'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'agenda_ids.*' => 'Kirim urutan semua agenda meeting.',
            'agenda_ids.*.*' => 'Setiap agenda harus muncul satu kali.',
        ];
    }
}
