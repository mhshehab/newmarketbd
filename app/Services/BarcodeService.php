<?php

namespace App\Services;

use App\Models\Product;

class BarcodeService
{
    public static function scanBarcode($barcode)
    {
        // Clean barcode input
        $barcode = trim($barcode);
        
        // Remove any non-digit characters for standard barcodes
        if (preg_match('/^\d+$/', $barcode)) {
            return Product::where('barcode', $barcode)->first();
        }
        
        // Handle QR codes or other formats
        if (self::isQRCode($barcode)) {
            return self::parseQRCode($barcode);
        }
        
        return null;
    }
    
    private static function isQRCode($barcode)
    {
        // QR codes are typically longer and may contain letters
        return strlen($barcode) > 12 || !preg_match('/^\d+$/', $barcode);
    }
    
    private static function parseQRCode($qrCode)
    {
        // Parse QR code data (format: product_id:quantity or product_sku)
        if (strpos($qrCode, ':') !== false) {
            list($productId, $quantity) = explode(':', $qrCode);
            $product = Product::find($productId);
            if ($product) {
                return $product;
            }
        }
        
        // Try to find by SKU
        return Product::where('sku', $qrCode)->first();
    }
    
    public static function generateBarcode($productId)
    {
        $product = Product::find($productId);
        if (!$product || $product->barcode) {
            return $product->barcode ?? null;
        }
        
        // Generate 9-digit barcode
        $barcode = rand(100000000, 999999999);
        
        // Ensure uniqueness
        while (Product::where('barcode', $barcode)->exists()) {
            $barcode = rand(100000000, 999999999);
        }
        
        $product->update(['barcode' => $barcode]);
        
        return $barcode;
    }
}
