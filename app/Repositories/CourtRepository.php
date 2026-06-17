<?php

namespace App\Repositories;

use App\Models\Court;
use App\Repositories\Contracts\CourtRepositoryInterface;

class CourtRepository implements CourtRepositoryInterface
{
    public function getAll(array $filters = [])
    {
        $query = Court::query()->with('photos');

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search']) && $filters['search'] !== '') {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->get();
    }

    public function findById(int $id): Court
    {
        return Court::with(['photos', 'priceOverrides', 'auditLogs.user'])->findOrFail($id);
    }

    public function create(array $data): Court
    {
        return Court::create($data);
    }

    public function update(int $id, array $data): Court
    {
        $court = $this->findById($id);
        $court->update($data);
        return $court;
    }

    public function delete(int $id): bool
    {
        $court = $this->findById($id);
        return $court->delete();
    }

    public function getActiveForPublic()
    {
        return Court::where('status', 'active')->with('photos')->get();
    }
}
