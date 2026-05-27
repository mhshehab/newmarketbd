<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_email',
        'testimonial_text',
        'rating',
        'customer_image',
        'is_active',
        'position'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rating' => 'integer',
        'position' => 'integer'
    ];
}
