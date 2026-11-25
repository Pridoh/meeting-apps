<?php

namespace App\Modules\Participation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveAttendanceRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'choice' => ['required', Rule::in(['will_attend', 'not_attending'])],
            'schedule_version' => ['required', 'integer', 'min:1'],
        ];
    }
}
