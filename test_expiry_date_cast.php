<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Expiry Date Cast ===" . PHP_EOL;

use App\Models\Product;

// Get products with expiry dates
$products = Product::whereNotNull('expiry_date')->get();

echo "Found {$products->count()} products with expiry dates:" . PHP_EOL;

foreach ($products as $product) {
    echo PHP_EOL . "Product: {$product->name}" . PHP_EOL;
    echo "Raw expiry_date: " . $product->expiry_date . PHP_EOL;
    echo "Data type: " . gettype($product->expiry_date) . PHP_EOL;
    
    // Test if it's a Carbon instance
    if ($product->expiry_date instanceof \Carbon\Carbon) {
        echo "Is Carbon instance: Yes" . PHP_EOL;
        echo "Formatted date: " . $product->expiry_date->format('d/m/Y') . PHP_EOL;
        echo "Formatted datetime: " . $product->expiry_date->format('Y-m-d H:i:s') . PHP_EOL;
    } else {
        echo "Is Carbon instance: No" . PHP_EOL;
        echo "Type: " . get_class($product->expiry_date) . PHP_EOL;
    }
    
    echo "---" . PHP_EOL;
}

echo PHP_EOL . "=== Test Date Comparison ===" . PHP_EOL;

// Test date comparison for expiry check
$thresholdDate = now()->addDays(15)->endOfDay();
echo "Threshold date: " . $thresholdDate->format('d/m/Y') . PHP_EOL;

foreach ($products as $product) {
    $isExpiring = $product->expiry_date <= $thresholdDate;
    echo "Product: {$product->name}" . PHP_EOL;
    echo "Expiry: " . $product->expiry_date->format('d/m/Y') . PHP_EOL;
    echo "Is expiring within 15 days: " . ($isExpiring ? 'Yes' : 'No') . PHP_EOL;
    echo "---" . PHP_EOL;
}
