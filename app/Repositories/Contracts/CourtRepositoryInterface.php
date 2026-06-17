<?php

namespace App\Repositories\Contracts;

use App\Models\Court;

interface CourtRepositoryInterface
{
    public function getAll(array $filters = []);
    public function findById(int $id): Court;
    public function create(array $data): Court;
    public function update(int $id, array $data): Court;
    public function delete(int $id): bool;
    public function getActiveForPublic();
}
