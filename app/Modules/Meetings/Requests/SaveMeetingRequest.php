<?php

namespace App\Modules\Meetings\Requests;

use App\Modules\Meetings\Models\Meeting;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $meeting = $this->route('meeting');

        return $meeting === null || $this->user()->can('update', $meeting);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'start_time' => ['required', 'date_format:Y-m-d\TH:i,Y-m-d\TH:i:s'],
            'end_time' => ['required', 'date_format:Y-m-d\TH:i,Y-m-d\TH:i:s'],
            'timezone' => ['sometimes', 'required', 'timezone'],
            'mode' => ['required', Rule::in(Meeting::MODES)],
            'location' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'title.required' => 'Masukkan judul meeting.',
            'title.max' => 'Judul maksimal 255 karakter.',
            'description.max' => 'Deskripsi maksimal 10.000 karakter.',
            'start_time.required' => 'Masukkan waktu mulai.',
            'start_time.date_format' => 'Masukkan waktu mulai yang valid.',
            'end_time.required' => 'Masukkan waktu selesai.',
            'end_time.date_format' => 'Masukkan waktu selesai yang valid.',
            'timezone.*' => 'Pilih zona waktu yang valid.',
            'mode.*' => 'Pilih mode online atau offline.',
            'location.max' => 'Lokasi atau tautan maksimal 255 karakter.',
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['start_time', 'end_time', 'timezone'])) {
                    return;
                }

                $start = $this->localDateTime('start_time');
                $end = $this->localDateTime('end_time');

                foreach (['start_time' => $start, 'end_time' => $end] as $field => $time) {
                    $local = $this->input($field);
                    if (! str_starts_with($time->format('Y-m-d\TH:i:s'), $local)) {
                        $validator->errors()->add($field, 'Waktu tersebut tidak tersedia dalam zona waktu yang dipilih.');
                    }
                }

                if ($end <= $start) {
                    $validator->errors()->add('end_time', 'Waktu selesai harus setelah waktu mulai.');
                }
            },
        ];
    }

    /** @return array{title: string, description?: string|null, start_time: string, end_time: string, timezone: string, mode: string, location?: string|null} */
    public function meetingData(): array
    {
        $utc = new DateTimeZone('UTC');

        return [
            ...$this->validated(),
            'start_time' => $this->localDateTime('start_time')->setTimezone($utc)->format('Y-m-d H:i:s'),
            'end_time' => $this->localDateTime('end_time')->setTimezone($utc)->format('Y-m-d H:i:s'),
            'timezone' => $this->input('timezone', $this->defaultTimezone()),
        ];
    }

    private function localDateTime(string $field): DateTimeImmutable
    {
        return new DateTimeImmutable($this->input($field), new DateTimeZone($this->input('timezone', $this->defaultTimezone())));
    }

    private function defaultTimezone(): string
    {
        return $this->route('meeting')?->timezone ?? Meeting::DEFAULT_TIMEZONE;
    }
}
