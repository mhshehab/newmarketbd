<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 
        'name', 
        'slug', 
        'sku', 
        'barcode', 
        'description', 
        'price', 
        'stock_quantity', 
        'expiry_date',
        'image', 
        'status'
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
        ];
    }

    /**
     * অটোমেটিক SKU এবং Barcode জেনারেট করার লজিক
     */
    protected static function booted()
    {
        static::creating(function ($product) {
            // যদি SKU খালি থাকে তবে অটো জেনারেট হবে
            if (empty($product->sku)) {
                $prefix = strtoupper(substr($product->name, 0, 3));
                $product->sku = $prefix . '-' . rand(1000, 9999) . '-' . time();
            }

            // যদি Barcode খালি থাকে তবে একটি র‍্যান্ডম নাম্বার বসবে
            if (empty($product->barcode)) {
                $product->barcode = rand(100000000, 999999999);
            }
        });
    }

    // ক্যাটাগরির সাথে সম্পর্ক
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    // প্রোডাক্ট ভ্যারিয়েন্টের সাথে সম্পর্ক
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}