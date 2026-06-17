<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'admin');

        if ($request->has('status') && $request->input('status') !== '') {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->has('search') && $request->input('search') !== '') {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $staff = $query->orderBy('name')->get();

        return Inertia::render('Owner/Staff/Index', [
            'staff' => $staff,
            'filters' => $request->only(['search', 'status'])
        ]);
    }
}
