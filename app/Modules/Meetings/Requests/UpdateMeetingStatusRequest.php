<?php

namespace App\Modules\Meetings\Requests;

use App\Modules\Meetings\Models\Meeting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMeetingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('meeting'));
    }

    /** @return array{status: list<mixed>} */
    public function rules(): array
    {
        return ['status' => ['required', Rule::in(Meeting::STATUSES)]];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['status.*' => 'Pilih status meeting yang valid.'];
    }
}
