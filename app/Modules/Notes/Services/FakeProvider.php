<?php

namespace App\Modules\Notes\Services;

class FakeProvider implements AiService
{
    public function generate(array $context, float $timeout): string
    {
        return json_encode([
            'meeting_summary' => 'Contoh draf untuk latihan review.',
            'key_discussion_points' => ['Periksa catatan asli sebelum finalisasi.'],
            'decisions' => [],
            'action_items' => [],
        ], JSON_THROW_ON_ERROR);
    }
}
