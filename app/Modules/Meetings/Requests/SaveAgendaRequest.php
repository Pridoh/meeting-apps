<?php

namespace App\Modules\Meetings\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveAgendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('meeting'));
    }

    /** @return array{title: list<string>, description: list<string>} */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'title.required' => 'Masukkan judul agenda.',
            'title.max' => 'Judul maksimal 255 karakter.',
            'description.max' => 'Deskripsi maksimal 10.000 karakter.',
        ];
    }
}
