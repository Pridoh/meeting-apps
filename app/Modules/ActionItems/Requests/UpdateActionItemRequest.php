<?php

namespace App\Modules\ActionItems\Requests;

use App\Modules\ActionItems\Models\ActionItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateActionItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = [$this->route('actionItem'), $this->route('meeting')];

        return $this->user()->can('update', $target)
            && (! $this->has('assignee_user_id') || $this->user()->can('assign', $target));
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(ActionItem::STATUSES)],
            'assignee_user_id' => ['sometimes', 'nullable', 'integer'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'status.*' => 'Pilih status tugas yang valid.',
            'assignee_user_id.*' => 'Pilih peserta dari meeting ini.',
        ];
    }
}
