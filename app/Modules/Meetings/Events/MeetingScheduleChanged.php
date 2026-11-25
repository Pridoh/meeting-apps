<?php

namespace App\Modules\Meetings\Events;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class MeetingScheduleChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly int $meetingId,
        public readonly int $scheduleVersion,
        public readonly CarbonImmutable $effectiveAt,
    ) {}
}
