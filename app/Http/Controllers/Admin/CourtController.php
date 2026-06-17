<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourtRequest;
use App\Http\Requests\UpdateCourtRequest;
use App\Http\Requests\StorePriceOverrideRequest;
use App\Services\CourtService;
use App\Models\CourtPhoto;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CourtController extends Controller
{
    protected $courtService;

    public function __construct(CourtService $courtService)
    {
        $this->courtService = $courtService;
    }

    /**
     * Display a listing of the courts.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'search']);
        $courts = $this->courtService->listCourts($filters);

        return Inertia::render('Admin/Courts/Index', [
            'courts' => $courts,
            'filters' => $filters
        ]);
    }

    /**
     * Show the form for creating a new court.
     */
    public function create()
    {
        return Inertia::render('Admin/Courts/Create');
    }

    /**
     * Store a newly created court in storage.
     */
    public function store(StoreCourtRequest $request)
    {
        $data = $request->validated();
        
        // Create court
        $court = $this->courtService->createCourt([
            'name' => $data['name'],
            'type' => $data['type'],
            'price' => $data['price'],
            'slot_duration' => $data['slot_duration'],
            'open_time' => $data['open_time'],
            'close_time' => $data['close_time'],
            'status' => $data['status'],
        ]);

        // Handle file uploads
        if ($request->hasFile('photos')) {
            $sortOrder = 0;
            foreach ($request->file('photos') as $photoFile) {
                $path = $photoFile->store('courts', 'public');
                CourtPhoto::create([
                    'court_id' => $court->id,
                    'path' => Storage::url($path),
                    'sort_order' => $sortOrder++,
                ]);
            }
        }

        return redirect()->route('admin.courts.index')->with('success', 'Lapangan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified court.
     */
    public function edit(int $id)
    {
        // Get court with photos, price overrides, and audit logs
        $court = Court::with(['photos', 'priceOverrides', 'auditLogs.user'])->findOrFail($id);

        return Inertia::render('Admin/Courts/Edit', [
            'court' => $court
        ]);
    }

    /**
     * Update the specified court in storage.
     */
    public function update(UpdateCourtRequest $request, int $id)
    {
        $data = $request->validated();
        
        // Update court info
        $court = $this->courtService->updateCourt($id, [
            'name' => $data['name'],
            'type' => $data['type'],
            'price' => $data['price'],
            'slot_duration' => $data['slot_duration'],
            'open_time' => $data['open_time'],
            'close_time' => $data['close_time'],
            'status' => $data['status'],
        ]);

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            // Get current max sort order
            $currentMaxOrder = CourtPhoto::where('court_id', $court->id)->max('sort_order') ?? -1;
            $sortOrder = $currentMaxOrder + 1;

            foreach ($request->file('photos') as $photoFile) {
                $path = $photoFile->store('courts', 'public');
                CourtPhoto::create([
                    'court_id' => $court->id,
                    'path' => Storage::url($path),
                    'sort_order' => $sortOrder++,
                ]);
            }
        }

        return redirect()->route('admin.courts.index')->with('success', 'Lapangan berhasil diperbarui.');
    }

    /**
     * Remove the specified court from storage.
     */
    public function destroy(int $id)
    {
        // Get court photos to delete physical files
        $photos = CourtPhoto::where('court_id', $id)->get();
        foreach ($photos as $photo) {
            // Convert /storage/courts/xyz.jpg to public/courts/xyz.jpg
            $storagePath = str_replace('/storage/', '', $photo->path);
            Storage::disk('public')->delete($storagePath);
        }

        // Delete court (cascades database-level delete for photos, overrides, logs)
        $this->courtService->updateCourt($id, ['status' => 'inactive']); // safety toggle or complete delete
        Court::findOrFail($id)->delete();

        return redirect()->route('admin.courts.index')->with('success', 'Lapangan berhasil dihapus.');
    }

    /**
     * Toggle the status of the court.
     */
    public function toggleStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:active,inactive,maintenance']
        ]);

        $this->courtService->toggleStatus($id, $request->input('status'));

        return redirect()->back()->with('success', 'Status lapangan berhasil diubah.');
    }

    /**
     * Store a price override for a court.
     */
    public function storePriceOverride(StorePriceOverrideRequest $request, int $id)
    {
        $data = $request->validated();
        
        $this->courtService->addPriceOverride(
            $id,
            $data['date'],
            (float) $data['price'],
            $data['note'] ?? null
        );

        return redirect()->back()->with('success', 'Override harga berhasil disimpan.');
    }

    /**
     * Delete an individual court photo.
     */
    public function deletePhoto(int $photoId)
    {
        $photo = CourtPhoto::findOrFail($photoId);
        $storagePath = str_replace('/storage/', '', $photo->path);
        Storage::disk('public')->delete($storagePath);
        $photo->delete();

        return redirect()->back()->with('success', 'Foto lapangan berhasil dihapus.');
    }
}
