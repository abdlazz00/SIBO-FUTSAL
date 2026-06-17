<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['customer_name', 'avatar', 'rating', 'content', 'is_active', 'sort_order'])]
class Testimonial extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
        'rating' => 'integer',
        'sort_order' => 'integer',
    ];
}
