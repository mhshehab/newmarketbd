<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',       // Customer or user ID
        'order_number',   // Auto-generated order number
        'total_amount',  // Total order amount
        'status',        // Order status
        'notes',         // Additional order notes
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

    /**
     * Get the order items for this order.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payments for this order.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the loyalty points for this order.
     */
    public function loyaltyPoints()
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    /**
     * Get the total quantity of items in the order.
     */
    public function getTotalQuantityAttribute()
    {
        return $this->orderItems()->sum('quantity');
    }

    /**
     * Check if order can be edited.
     */
    public function canBeEdited()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if order can be cancelled.
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'processing']);
    }
}