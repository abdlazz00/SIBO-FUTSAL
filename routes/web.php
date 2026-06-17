<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Public\GuestBookingController;
use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Admin\CourtController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Owner\ReportController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\StaffController as OwnerStaffController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/courts/{id}/slots', [LandingController::class, 'getCourtSlots'])->name('courts.slots');

// Public Booking routes
Route::get('/booking', [GuestBookingController::class, 'showForm'])->name('booking.create');
Route::post('/booking', [GuestBookingController::class, 'store'])->name('booking.store');
Route::get('/booking/success/{booking_number}', [GuestBookingController::class, 'success'])->name('booking.success');
Route::get('/booking/check', [GuestBookingController::class, 'showTrackForm'])->name('booking.track.show');
Route::post('/booking/check', [GuestBookingController::class, 'track'])->name('booking.track.submit');

// Dynamic dashboard redirect based on role
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->isOwner()) {
        return redirect()->route('owner.dashboard');
    } elseif ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    } else {
        return redirect()->route('customer.dashboard');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin & Owner group
Route::prefix('admin')->middleware(['auth', 'role:admin,owner'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Courts management
    Route::get('/courts', [CourtController::class, 'index'])->name('admin.courts.index');
    Route::get('/courts/create', [CourtController::class, 'create'])->name('admin.courts.create');
    Route::post('/courts', [CourtController::class, 'store'])->name('admin.courts.store');
    Route::get('/courts/{id}/edit', [CourtController::class, 'edit'])->name('admin.courts.edit');
    Route::put('/courts/{id}', [CourtController::class, 'update'])->name('admin.courts.update');
    Route::delete('/courts/{id}', [CourtController::class, 'destroy'])->name('admin.courts.destroy');
    Route::patch('/courts/{id}/status', [CourtController::class, 'toggleStatus'])->name('admin.courts.status');
    Route::post('/courts/{id}/price-override', [CourtController::class, 'storePriceOverride'])->name('admin.courts.price-override');
    Route::delete('/courts/photos/{photo_id}', [CourtController::class, 'deletePhoto'])->name('admin.courts.photos.destroy');

    // Testimonials management
    Route::get('/testimonials', [TestimonialController::class, 'index'])->name('admin.testimonials.index');
    Route::post('/testimonials', [TestimonialController::class, 'store'])->name('admin.testimonials.store');
    Route::put('/testimonials/{id}', [TestimonialController::class, 'update'])->name('admin.testimonials.update');
    Route::delete('/testimonials/{id}', [TestimonialController::class, 'destroy'])->name('admin.testimonials.destroy');

    // Bookings management
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('admin.bookings.index');
    Route::post('/bookings', [AdminBookingController::class, 'store'])->name('admin.bookings.store');
    Route::patch('/bookings/{id}/reschedule', [AdminBookingController::class, 'reschedule'])->name('admin.bookings.reschedule');
    Route::patch('/bookings/{id}/cancel', [AdminBookingController::class, 'cancel'])->name('admin.bookings.cancel');
    Route::get('/bookings/export', [AdminBookingController::class, 'export'])->name('admin.bookings.export');

    // Payments management
    Route::get('/payments', [PaymentController::class, 'index'])->name('admin.payments.index');
    Route::post('/payments/{booking_id}/confirm', [PaymentController::class, 'confirm'])->name('admin.payments.confirm');
    Route::post('/payments/{payment_id}/refund', [PaymentController::class, 'refund'])->name('admin.payments.refund');
    Route::get('/payments/export', [PaymentController::class, 'export'])->name('admin.payments.export');

    // Expenses management
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('admin.expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('admin.expenses.store');
    Route::put('/expenses/{id}', [ExpenseController::class, 'update'])->name('admin.expenses.update');
    Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy'])->name('admin.expenses.destroy');
});

// Owner group (exclusive)
Route::prefix('owner')->middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('owner.dashboard');

    // Financial Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('owner.reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('owner.reports.export');

    // Staff Management
    Route::get('/staff', [OwnerStaffController::class, 'index'])->name('owner.staff.index');
});

// Customer group
Route::prefix('customer')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('customer.bookings.index'); // Redirect to bookings history
    })->name('customer.dashboard');

    Route::get('/bookings', [CustomerBookingController::class, 'index'])->name('customer.bookings.index');
    Route::delete('/bookings/{id}', [CustomerBookingController::class, 'cancel'])->name('customer.bookings.cancel');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

require __DIR__.'/auth.php';
