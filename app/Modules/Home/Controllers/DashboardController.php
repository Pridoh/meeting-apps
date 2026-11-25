<?php

namespace App\Modules\Home\Controllers;

use App\Modules\ActionItems\Models\ActionItem;
use App\Modules\Meetings\Models\Meeting;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $ownedMeetings = Meeting::query()->whereBelongsTo($user, 'organizer');
        $participantTasks = ActionItem::assignedTo($user);
        $pendingTasks = ActionItem::query()
            ->whereHas('meeting', fn (Builder $meetings): Builder => $meetings->whereBelongsTo($user, 'organizer'))
            ->where('status', '!=', 'completed');

        return view('dashboard', [
            'organizerStats' => [
                'total' => (clone $ownedMeetings)->count(),
                'upcoming' => (clone $ownedMeetings)->upcoming()->count(),
                'completed' => (clone $ownedMeetings)->where('status', 'completed')->count(),
                'pendingTasks' => $pendingTasks->count(),
            ],
            'recentMeetings' => $ownedMeetings->latest()->orderByDesc('id')->limit(5)->get(),
            'upcomingMeetings' => Meeting::upcoming()
                ->whereHas('participants', fn (Builder $participants): Builder => $participants->whereKey($user->id))
                ->with('organizer')
                ->orderBy('start_time')
                ->orderBy('id')
                ->limit(5)
                ->get(),
            'participantTasks' => (clone $participantTasks)->with('meeting')->latest()->orderByDesc('id')->limit(5)->get(),
            'participantTaskCount' => (clone $participantTasks)->count(),
            'completedTaskCount' => (clone $participantTasks)->where('status', 'completed')->count(),
            'nearestDeadline' => $participantTasks->where('status', '!=', 'completed')
                ->whereDate('deadline', '>=', today())
                ->min('deadline'),
        ]);
    }
}
