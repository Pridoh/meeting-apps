<?php

namespace App\Modules\Notes\Services;

interface AiService
{
    public function generate(array $context, float $timeout): string;
}
