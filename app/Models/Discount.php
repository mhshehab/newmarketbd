<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type', // 'percentage', 'fixed', 'buy_one_get_one', 'bundle'
        'value',
        'minimum_amount',
        'maximum_discount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'applicable_products', // JSON array of product IDs
        'applicable_categories', // JSON array of category IDs
        'buy_quantity',
        'get_quantity',
        'get_percentage', // for BOGO offers
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'applicable_products' => 'array',
        'applicable_categories' => 'array',
        'buy_quantity' => 'integer',
        'get_quantity' => 'integer',
        'get_percentage' => 'decimal:2',
    ];

    public function isValid()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && now()->isBefore($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && now()->isAfter($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function calculateDiscount($cartTotal, $cartItems = [])
    {
        if (!$this->isValid()) {
            return 0;
        }

        switch ($this->type) {
            case 'percentage':
                $discount = $cartTotal * ($this->value / 100);
                if ($this->maximum_discount && $discount > $this->maximum_discount) {
                    $discount = $this->maximum_discount;
                }
                return $discount;

            case 'fixed':
                if ($this->minimum_amount && $cartTotal < $this->minimum_amount) {
                    return 0;
                }
                return min($this->value, $cartTotal);

            case 'buy_one_get_one':
                return $this->calculateBOGODiscount($cartItems);

            case 'bundle':
                return $this->calculateBundleDiscount($cartItems);

            default:
                return 0;
        }
    }

    private function calculateBOGODiscount($cartItems)
    {
        $discount = 0;
        $applicableProducts = $this->applicable_products ?? [];

        foreach ($cartItems as $item) {
            if (!empty($applicableProducts) && !in_array($item['product_id'], $applicableProducts)) {
                continue;
            }

            $buyQty = $this->buy_quantity ?? 1;
            $getQty = $this->get_quantity ?? 1;
            $getPercentage = $this->get_percentage ?? 100;

            if ($item['quantity'] >= $buyQty + $getQty) {
                $sets = intval($item['quantity'] / ($buyQty + $getQty));
                $discount += $sets * $getQty * $item['price'] * ($getPercentage / 100);
            }
        }

        return $discount;
    }

    private function calculateBundleDiscount($cartItems)
    {
        $discount = 0;
        $applicableProducts = $this->applicable_products ?? [];

        foreach ($cartItems as $item) {
            if (!empty($applicableProducts) && !in_array($item['product_id'], $applicableProducts)) {
                continue;
            }

            $discount += $item['price'] * $item['quantity'] * ($this->value / 100);
        }

        return $discount;
    }

    public function applyDiscount()
    {
        $this->increment('used_count');
    }

    public static function findByCode($code)
    {
        return self::where('code', $code)->first();
    }

    public static function getActiveDiscounts()
    {
        return self::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->get();
    }

    public function getApplicableDiscounts($cartTotal, $cartItems = [])
    {
        $discounts = [];

        foreach (self::getActiveDiscounts() as $discount) {
            $discountAmount = $discount->calculateDiscount($cartTotal, $cartItems);
            if ($discountAmount > 0) {
                $discounts[] = [
                    'discount' => $discount,
                    'amount' => $discountAmount,
                ];
            }
        }

        return $discounts;
    }
}
