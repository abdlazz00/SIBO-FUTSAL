<?php

namespace App\Observers;

use App\Models\Court;
use App\Models\CourtAuditLog;
use Illuminate\Support\Facades\Auth;

class CourtAuditLogObserver
{
    /**
     * Handle the Court "created" event.
     */
    public function created(Court $court): void
    {
        $userId = Auth::id() ?? 1;

        CourtAuditLog::create([
            'court_id' => $court->id,
            'user_id' => $userId,
            'action' => 'create',
            'field_name' => null,
            'old_value' => null,
            'new_value' => json_encode($court->only(['name', 'type', 'price', 'slot_duration', 'open_time', 'close_time', 'status'])),
        ]);
    }

    /**
     * Handle the Court "updated" event.
     */
    public function updated(Court $court): void
    {
        $userId = Auth::id() ?? 1;

        foreach ($court->getChanges() as $field => $newValue) {
            if ($field === 'updated_at') {
                continue;
            }

            $oldValue = $court->getOriginal($field);

            CourtAuditLog::create([
                'court_id' => $court->id,
                'user_id' => $userId,
                'action' => 'update',
                'field_name' => $field,
                'old_value' => is_scalar($oldValue) ? (string) $oldValue : json_encode($oldValue),
                'new_value' => is_scalar($newValue) ? (string) $newValue : json_encode($newValue),
            ]);
        }
    }

    /**
     * Handle the Court "deleted" event.
     */
    public function deleted(Court $court): void
    {
        $userId = Auth::id() ?? 1;

        CourtAuditLog::create([
            'court_id' => $court->id,
            'user_id' => $userId,
            'action' => 'delete',
            'field_name' => null,
            'old_value' => json_encode($court->getOriginal()),
            'new_value' => null,
        ]);
    }
}
