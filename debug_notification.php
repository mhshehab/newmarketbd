<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Notification System ===" . PHP_EOL;

// 1. Check if there are any users
$usersCount = DB::table('users')->count();
echo "Total users: " . $usersCount . PHP_EOL;

// 2. Check if there are any products with expiry dates
$productsCount = DB::table('products')
    ->whereNotNull('expiry_date')
    ->where('expiry_date', '<=', now()->addDays(15)->endOfDay())
    ->count();

echo "Products expiring within 15 days: " . $productsCount . PHP_EOL;

// 3. Check if there are any products at all
$totalProducts = DB::table('products')->count();
echo "Total products: " . $totalProducts . PHP_EOL;

// 4. Check products with expiry dates
$productsWithExpiry = DB::table('products')
    ->whereNotNull('expiry_date')
    ->count();

echo "Products with expiry dates: " . $productsWithExpiry . PHP_EOL;

// 5. Show some sample products with expiry dates
$sampleProducts = DB::table('products')
    ->whereNotNull('expiry_date')
    ->limit(3)
    ->get();

echo PHP_EOL . "Sample products with expiry dates:" . PHP_EOL;
foreach ($sampleProducts as $product) {
    echo "- ID: {$product->id}, Name: {$product->name}, Expiry: {$product->expiry_date}" . PHP_EOL;
}
