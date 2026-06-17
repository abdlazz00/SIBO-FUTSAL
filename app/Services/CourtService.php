<?php

namespace App\Services;

use App\Models\Court;
use App\Models\CourtPriceOverride;
use App\Repositories\Contracts\CourtRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CourtService
{
    protected $courtRepository;

    public function __construct(CourtRepositoryInterface $courtRepository)
    {
        $this->courtRepository = $courtRepository;
    }

    public function listCourts(array $filters = [])
    {
        return $this->courtRepository->getAll($filters);
    }

    public function createCourt(array $data)
    {
        return $this->courtRepository->create($data);
    }

    public function updateCourt(int $id, array $data)
    {
        return $this->courtRepository->update($id, $data);
    }

    public function toggleStatus(int $id, string $status)
    {
        return $this->courtRepository->update($id, ['status' => $status]);
    }

    public function addPriceOverride(int $courtId, string $date, float $price, ?string $note = null, int $userId = null)
    {
        if (!$userId) {
            $userId = Auth::id();
        }

        return CourtPriceOverride::updateOrCreate(
            ['court_id' => $courtId, 'date' => $date],
            [
                'price' => $price,
                'note' => $note,
                'created_by' => $userId
            ]
        );
    }

    public function getAuditLogs(int $courtId)
    {
        $court = $this->courtRepository->findById($courtId);
        return $court->auditLogs()->with('user')->get();
    }

    /**
     * Generate all fixed time slots for a court on a specific date.
     */
    public function generateSlots(Court $court, string $date): array
    {
        // 1. Determine the price for this date (check price override)
        $override = CourtPriceOverride::where('court_id', $court->id)
            ->whereDate('date', $date)
            ->first();

        $price = $override ? (float) $override->price : (float) $court->price;

        // 2. Generate slots from open_time to close_time
        $slots = [];
        $startTime = Carbon::createFromFormat('H:i:s', $court->open_time);
        $endTime = Carbon::createFromFormat('H:i:s', $court->close_time);
        $duration = $court->slot_duration; // duration in minutes

        $current = $startTime->copy();

        while ($current->copy()->addMinutes($duration)->lte($endTime)) {
            $slotStart = $current->format('H:i:s');
            $current->addMinutes($duration);
            $slotEnd = $current->format('H:i:s');

            $slots[] = [
                'start_time' => $slotStart,
                'end_time' => $slotEnd,
                'formatted_time' => Carbon::createFromFormat('H:i:s', $slotStart)->format('H:i') . ' - ' . Carbon::createFromFormat('H:i:s', $slotEnd)->format('H:i'),
                'price' => $price,
                'is_override' => $override ? true : false,
                'override_note' => $override ? $override->note : null,
            ];
        }

        return $slots;
    }
}
