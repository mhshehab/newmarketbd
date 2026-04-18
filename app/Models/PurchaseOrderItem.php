<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'quantity_received',
        'unit_price',
        'total_price',
        'expiry_date',
        'batch_number',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getPendingQuantityAttribute()
    {
        return $this->quantity - $this->quantity_received;
    }

    public function getReceivedPercentageAttribute()
    {
        if ($this->quantity == 0) return 0;
        return ($this->quantity_received / $this->quantity) * 100;
    }

    public function isFullyReceived()
    {
        return $this->quantity_received >= $this->quantity;
    }

    public function receiveQuantity($quantity, $expiryDate = null, $batchNumber = null)
    {
        $maxReceiveable = $this->pending_quantity;
        $actualReceive = min($quantity, $maxReceiveable);

        $this->update([
            'quantity_received' => $this->quantity_received + $actualReceive,
            'expiry_date' => $expiryDate ?? $this->expiry_date,
            'batch_number' => $batchNumber ?? $this->batch_number,
        ]);

        return $actualReceive;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $item->total_price = $item->quantity * $item->unit_price;
        });

        static::updating(function ($item) {
            if ($item->isDirty(['quantity', 'unit_price'])) {
                $item->total_price = $item->quantity * $item->unit_price;
            }
        });
    }
}
