<?php

namespace App\Modules\Notes\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NotesContent
{
    public static function validate(array $data): array
    {
        return Validator::make($data, [
            'meeting_summary' => ['required', 'string', 'max:100000'],
            'key_discussion_points' => ['present', 'array', 'list'],
            'key_discussion_points.*' => ['required', 'string', 'max:16000'],
            'decisions' => ['present', 'array', 'list'],
            'decisions.*' => ['required', 'string', 'max:16000'],
            'action_items' => ['present', 'array', 'list'],
            'action_items.*' => ['array:description,assignee,deadline,status'],
            'action_items.*.description' => ['required', 'string', 'max:16000'],
            'action_items.*.assignee' => ['present', 'nullable', 'string', 'max:255'],
            'action_items.*.deadline' => ['present', 'nullable', 'date_format:Y-m-d'],
            'action_items.*.status' => ['required', 'in:pending'],
        ])->validate();
    }

    public static function fromProvider(AiService $provider, array $context): array
    {
        $expires = microtime(true) + 30;
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $remaining = $expires - microtime(true);
            if ($remaining <= 0) {
                break;
            }
            $json = $provider->generate($context, $remaining);
            try {
                $object = json_decode($json, false, flags: JSON_THROW_ON_ERROR);
                if (! is_object($object) || ! is_array($object->decisions ?? null)
                    || ! is_array($object->action_items ?? null) || ! is_array($object->key_discussion_points ?? null)) {
                    continue;
                }

                return self::validate(json_decode($json, true, flags: JSON_THROW_ON_ERROR));
            } catch (\JsonException|ValidationException) {
                // Satu percobaan ulang untuk respons yang formatnya tidak sesuai.
            }
        }

        throw ValidationException::withMessages(['ai' => 'Struktur jawaban AI tidak valid. Draf Anda tersimpan. Coba lagi.']);
    }
}
