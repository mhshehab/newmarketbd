<?php

namespace App\Services;

use App\Models\Product;

class WeighingScaleService
{
    // Simulate weighing scale connection
    public static function getWeight()
    {
        // In real implementation, this would connect to actual weighing scale
        // For now, return simulated weight or get from hardware
        return [
            'weight' => 0.0,
            'unit' => 'kg',
            'stable' => true
        ];
    }
    
    public static function calculatePrice($productId, $weight)
    {
        $product = Product::find($productId);
        
        if (!$product || !$product->is_weighted_product) {
            return null;
        }
        
        // Calculate price based on weight and per-unit price
        $pricePerKg = $product->price;
        $totalPrice = $weight * $pricePerKg;
        
        return [
            'product_id' => $productId,
            'product_name' => $product->name,
            'weight' => $weight,
            'unit' => 'kg',
            'price_per_kg' => $pricePerKg,
            'total_price' => round($totalPrice, 2),
            'formatted_price' => 'Tk ' . number_format($totalPrice, 2)
        ];
    }
    
    public static function addToCartByWeight($productId, $weight, &$cart)
    {
        $product = Product::find($productId);
        
        if (!$product || !$product->is_weighted_product) {
            return false;
        }
        
        if ($product->stock_quantity < $weight) {
            return false;
        }
        
        $priceData = self::calculatePrice($productId, $weight);
        
        if ($priceData) {
            $cart[$productId] = [
                'name' => $product->name,
                'price' => $priceData['total_price'],
                'quantity' => $weight,
                'is_weighted' => true,
                'unit' => 'kg',
                'weight_per_unit' => $product->weight_per_unit
            ];
            
            return true;
        }
        
        return false;
    }
    
    // Simulate hardware connection (for real implementation)
    public static function connectToScale()
    {
        // This would connect to actual weighing scale hardware
        // Examples: COM port, USB, Bluetooth, etc.
        return true;
    }
    
    public static function disconnectFromScale()
    {
        // Disconnect from weighing scale
        return true;
    }
    
    public static function isScaleConnected()
    {
        // Check if scale is connected
        return true; // For simulation
    }
}
