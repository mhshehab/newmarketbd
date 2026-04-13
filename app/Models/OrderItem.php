<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    // এই অংশটুকু যোগ করুন
    protected $fillable = [
        'order_id',   // অর্ডারের আইডি
        'product_id', // পণ্যের আইডি
        'quantity',   // পরিমাণ
        'unit_price', // মূল্য
    ];

    /**
     * অর্ডারের সাথে সম্পর্ক
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * প্রোডাক্টের সাথে সম্পর্ক
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}