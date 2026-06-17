<?php

namespace App\Repositories;

use App\Models\Testimonial;
use App\Repositories\Contracts\TestimonialRepositoryInterface;

class TestimonialRepository implements TestimonialRepositoryInterface
{
    public function getAll()
    {
        return Testimonial::orderBy('sort_order', 'asc')->get();
    }

    public function getActive()
    {
        return Testimonial::where('is_active', true)->orderBy('sort_order', 'asc')->get();
    }

    public function find(int $id): Testimonial
    {
        return Testimonial::findOrFail($id);
    }

    public function create(array $data): Testimonial
    {
        return Testimonial::create($data);
    }

    public function update(int $id, array $data): Testimonial
    {
        $testimonial = $this->find($id);
        $testimonial->update($data);
        return $testimonial;
    }

    public function delete(int $id): bool
    {
        $testimonial = $this->find($id);
        return $testimonial->delete();
    }
}
