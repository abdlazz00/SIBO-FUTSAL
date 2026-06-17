<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['court_id', 'path', 'sort_order'])]
class CourtPhoto extends Model
{
    // Turn off timestamps since the migration only has created_at
    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    /**
     * Get the court that owns the photo.
     */
    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }
}
