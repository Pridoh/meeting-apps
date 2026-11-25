<?php

namespace App\Modules\Meetings\Controllers;

use App\Modules\Meetings\Models\Meeting;
use App\Modules\Meetings\Requests\ReorderAgendaRequest;
use App\Modules\Meetings\Requests\SaveAgendaRequest;
use App\Modules\Meetings\Services\AgendaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class AgendaController
{
    public function store(SaveAgendaRequest $request, Meeting $meeting, AgendaService $service): RedirectResponse
    {
        $service->create($meeting, $request->validated());

        return to_route('meetings.show', $meeting)->with('success', 'Agenda ditambahkan.');
    }

    public function update(SaveAgendaRequest $request, Meeting $meeting, int $agenda, AgendaService $service): RedirectResponse
    {
        $service->update($meeting, $agenda, $request->validated());

        return to_route('meetings.show', $meeting)->with('success', 'Agenda diperbarui.');
    }

    public function destroy(Meeting $meeting, int $agenda, AgendaService $service): RedirectResponse
    {
        Gate::authorize('update', $meeting);
        $service->delete($meeting, $agenda);

        return to_route('meetings.show', $meeting)->with('success', 'Agenda dihapus.');
    }

    public function reorder(ReorderAgendaRequest $request, Meeting $meeting, AgendaService $service): RedirectResponse
    {
        $service->reorder($meeting, $request->validated('agenda_ids'));

        return to_route('meetings.show', $meeting)->with('success', 'Urutan agenda diperbarui.');
    }
}
