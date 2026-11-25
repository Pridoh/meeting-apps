<?php

namespace App\Modules\ActionItems\Controllers;

use App\Modules\ActionItems\Models\ActionItem;
use App\Modules\ActionItems\Requests\UpdateActionItemRequest;
use App\Modules\ActionItems\Services\ActionItemService;
use App\Modules\Meetings\Models\Meeting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActionItemController
{
    public function index(Request $request): View
    {
        return view('action-items.index', [
            'actionItems' => ActionItem::visibleTo($request->user())
                ->with('meeting', 'assignee')
                ->orderByRaw('CASE WHEN deadline IS NULL THEN 1 ELSE 0 END')
                ->orderBy('deadline')
                ->orderBy('id')
                ->paginate(15),
        ]);
    }

    public function update(UpdateActionItemRequest $request, Meeting $meeting, ActionItem $actionItem, ActionItemService $service): RedirectResponse
    {
        $service->update($meeting, $actionItem, $request->user(), $request->validated());

        return back()->with('success', 'Tugas diperbarui.');
    }
}
