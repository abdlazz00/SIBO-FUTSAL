<?php

namespace App\Repositories\Contracts;

use App\Models\Testimonial;

interface TestimonialRepositoryInterface
{
    public function getAll();
    public function getActive();
    public function find(int $id): Testimonial;
    public function create(array $data): Testimonial;
    public function update(int $id, array $data): Testimonial;
    public function delete(int $id): bool;
}
