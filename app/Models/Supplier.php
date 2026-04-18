<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\PurchaseOrder;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'contact_person',
        'notes',
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function getActiveOrdersCount()
    {
        return $this->purchaseOrders()
            ->whereIn('status', ['pending', 'confirmed', 'partial_received'])
            ->count();
    }

    public function getTotalPurchaseAmount()
    {
        return $this->purchaseOrders()
            ->where('status', 'received')
            ->sum('final_amount');
    }

    public function getAverageDeliveryTime()
    {
        $completedOrders = $this->purchaseOrders()
            ->where('status', 'received')
            ->whereNotNull('expected_delivery_date')
            ->get();

        if ($completedOrders->isEmpty()) {
            return 0;
        }

        $totalDays = $completedOrders->sum(function ($order) {
            return $order->created_at->diffInDays($order->expected_delivery_date);
        });

        return $totalDays / $completedOrders->count();
    }
}
