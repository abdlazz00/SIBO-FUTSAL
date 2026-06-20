<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $flash = $request->session()->get('flash') ?? [];
        \Log::info('HandleInertiaRequests share flash data:', ['flash' => $flash, 'success' => $request->session()->get('success')]);
        if (is_string($flash)) {
            $flash = ['success' => $flash];
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'success' => $request->session()->get('success') ?? ($flash['success'] ?? null),
                'error' => $request->session()->get('error') ?? ($flash['error'] ?? null),
                'info' => $request->session()->get('info') ?? ($flash['info'] ?? null),
                'booking_number' => $request->session()->get('booking_number') ?? ($flash['booking_number'] ?? null),
                'total_price' => $request->session()->get('total_price') ?? ($flash['total_price'] ?? null),
                'confirmed_payment' => $request->session()->get('confirmed_payment') ?? ($flash['confirmed_payment'] ?? null),
            ],
        ];
    }
}
