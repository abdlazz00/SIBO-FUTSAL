<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'type', 'price', 'slot_duration', 'open_time', 'close_time', 'status'])]
class Court extends Model
{
    /**
     * Get the photos for the court.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(CourtPhoto::class)->orderBy('sort_order', 'asc');
    }

    /**
     * Get the price overrides for the court.
     */
    public function priceOverrides(): HasMany
    {
        return $this->hasMany(CourtPriceOverride::class);
    }

    /**
     * Get the audit logs for the court.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(CourtAuditLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the bookings for the court.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
