<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\TestimonialRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TestimonialController extends Controller
{
    protected $testimonialRepo;

    public function __construct(TestimonialRepositoryInterface $testimonialRepo)
    {
        $this->testimonialRepo = $testimonialRepo;
    }

    public function index()
    {
        $testimonials = $this->testimonialRepo->getAll();
        return Inertia::render('Admin/Testimonials/Index', [
            'testimonials' => $testimonials
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer'],
        ]);

        $this->testimonialRepo->create($data);

        return redirect()->back()->with('success', 'Testimoni berhasil ditambahkan.');
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer'],
        ]);

        $this->testimonialRepo->update($id, $data);

        return redirect()->back()->with('success', 'Testimoni berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $this->testimonialRepo->delete($id);
        return redirect()->back()->with('success', 'Testimoni berhasil dihapus.');
    }
}
