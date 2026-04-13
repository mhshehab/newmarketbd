<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',       // কাস্টমার বা ইউজারের আইডি
        'order_number',   // অটো-জেনারেটেড অর্ডার নম্বর
        'total_amount',  // অর্ডারের মোট টাকা
        'status',        // অর্ডারের অবস্থা
    ];

    /**
     * মডেল বুট হওয়ার সময় অটোমেটিক অর্ডার নম্বর জেনারেট করা
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            // সর্বশেষ অর্ডারের আইডি নেওয়া
            $latestOrder = self::latest('id')->first();
            $nextId = $latestOrder ? $latestOrder->id + 1 : 1;

            // ফরম্যাট: ORD-2026-0001
            $order->order_number = 'ORD-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * ইউজারের সাথে রিলেশনশিপ (যাতে কাস্টমারের নাম লিস্টে দেখানো যায়)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}