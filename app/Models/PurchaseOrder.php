<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'order_number',
        'order_date',
        'expected_delivery_date',
        'status', // 'pending', 'confirmed', 'partial_received', 'received', 'cancelled'
        'total_amount',
        'discount_amount',
        'tax_amount',
        'final_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $latestOrder = self::latest('id')->first();
            $nextId = $latestOrder ? $latestOrder->id + 1 : 1;
            $order->order_number = 'PO-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        });
    }

    public function updateStatus($status)
    {
        $this->update(['status' => $status]);
        
        // Auto-update stock when order is received
        if ($status === 'received') {
            $this->processReceivedItems();
        }
    }

    public function processReceivedItems()
    {
        foreach ($this->items as $item) {
            if ($item->quantity_received > 0) {
                // Update product stock
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock_quantity', $item->quantity_received);
                    
                    // Record stock movement
                    StockMovement::recordMovement(
                        $item->product_id,
                        'purchase',
                        $item->quantity_received,
                        $this->id,
                        self::class,
                        "Purchase Order #{$this->order_number}",
                        auth()->id()
                    );
                }
            }
        }
    }

    public function getPendingItemsCount()
    {
        return $this->items()->whereColumn('quantity_received', '<', 'quantity')->count();
    }

    public function getTotalReceivedAmount()
    {
        return $this->items()->sum('quantity_received * unit_price');
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function canBeReceived()
    {
        return in_array($this->status, ['confirmed', 'partial_received']);
    }

    public static function getPendingOrders()
    {
        return self::whereIn('status', ['pending', 'confirmed'])->get();
    }

    public static function getOverdueOrders()
    {
        return self::where('expected_delivery_date', '<', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();
    }
}
