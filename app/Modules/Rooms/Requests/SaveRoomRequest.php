<?php

namespace App\Modules\Rooms\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('meeting'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'organization_id' => ['required', 'integer', 'min:1'],
            'room_id' => ['required', 'integer', 'min:1'],
            'people_count' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'purpose' => ['required_if:intent,submit', 'nullable', 'string', 'max:3000'],
            'intent' => ['required', 'in:draft,submit'],
            'version' => ['required', 'integer', 'min:0'],
            'schedule_version' => ['required', 'integer', 'min:1'],
            'attachments' => ['sometimes', 'array', 'max:3'],
            'attachments.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
