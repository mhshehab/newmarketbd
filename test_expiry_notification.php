<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Expiry Notification ===" . PHP_EOL;

use App\Models\Product;
use App\Models\User;

// Clear existing notifications first
DB::table('notifications')->delete();
echo "Cleared existing notifications." . PHP_EOL;

// Find expiring products
$thresholdDate = now()->addDays(15)->endOfDay();
$expiringProducts = Product::whereNotNull('expiry_date')
    ->where('expiry_date', '<=', $thresholdDate)
    ->get();

echo "Found {$expiringProducts->count()} expiring products." . PHP_EOL;

if ($expiringProducts->isEmpty()) {
    echo "No expiring products found." . PHP_EOL;
    exit;
}

// Get admins
$admins = User::all();
echo "Found {$admins->count()} admin users." . PHP_EOL;

// Create Filament notifications
foreach ($expiringProducts as $product) {
    foreach ($admins as $admin) {
        echo "Creating notification for: {$product->name}" . PHP_EOL;
        
        // Use Filament Notification format
        \Filament\Notifications\Notification::make()
            ->title('Product Expiry Warning')
            ->body("Product: {$product->name} (Expiry: " . $product->expiry_date->format('d/m/Y') . ")")
            ->warning()
            ->icon('heroicon-o-calendar')
            ->sendToDatabase($admin);
    }
}

// Verify notifications
$count = DB::table('notifications()->count();
echo "Total notifications created: {$count}" . PHP_EOL;

// Show sample notification
$sample = DB::table('notifications')->first();
if ($sample) {
    echo PHP_EOL . "Sample notification:" . PHP_EOL;
    echo "ID: {$sample->id}" . PHP_EOL;
    echo "Type: {$sample->type}" . PHP_EOL;
    echo "Data: {$sample->data}" . PHP_EOL;
}

echo PHP_EOL . "=== Ready to Test ===" . PHP_EOL;
echo "1. Go to: http://127.0.0.1:8001/admin/login" . PHP_EOL;
echo "2. Login with your credentials" . PHP_EOL;
echo "3. Check notification bell in top-right corner" . PHP_EOL;
echo "4. You should see 'Product Expiry Warning' notifications" . PHP_EOL;
