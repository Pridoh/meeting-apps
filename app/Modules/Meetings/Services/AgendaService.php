<?php

namespace App\Modules\Meetings\Services;

use App\Modules\Meetings\Models\Meeting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgendaService
{
    /** @param array{title: string, description?: string|null} $details */
    public function create(Meeting $meeting, array $details): void
    {
        DB::transaction(function () use ($meeting, $details): void {
            $current = Meeting::lockForUpdate()->findOrFail($meeting->id);
            $nextIndex = $current->agendas()->reorder()->max('order_index') + 1;
            $current->agendas()->create([...$details, 'order_index' => $nextIndex]);
        });
    }

    /** @param array{title: string, description?: string|null} $details */
    public function update(Meeting $meeting, int $agendaId, array $details): void
    {
        DB::transaction(function () use ($meeting, $agendaId, $details): void {
            $current = Meeting::lockForUpdate()->findOrFail($meeting->id);
            $current->agendas()->findOrFail($agendaId)->update($details);
        });
    }

    public function delete(Meeting $meeting, int $agendaId): void
    {
        DB::transaction(function () use ($meeting, $agendaId): void {
            $current = Meeting::lockForUpdate()->findOrFail($meeting->id);
            $current->agendas()->findOrFail($agendaId)->delete();
        });
    }

    /** @param list<int|string> $agendaIds */
    public function reorder(Meeting $meeting, array $agendaIds): void
    {
        DB::transaction(function () use ($meeting, $agendaIds): void {
            $current = Meeting::lockForUpdate()->findOrFail($meeting->id);
            $agendas = $current->agendas()->get()->keyBy('id');
            if (count($agendaIds) !== $agendas->count() || array_diff($agendaIds, $agendas->keys()->all()) !== []) {
                throw ValidationException::withMessages(['agenda_ids' => 'Urutkan semua agenda dari meeting ini.']);
            }

            foreach ($agendaIds as $position => $agendaId) {
                $agendas[$agendaId]->update(['order_index' => $position + 1]);
            }
        });
    }
}
