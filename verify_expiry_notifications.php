<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verify Expiry Notifications ===" . PHP_EOL;

use App\Models\Product;
use App\Models\User;

// Test the query logic from CheckProductExpiry
$today = now()->startOfDay();
$thresholdDate = now()->addDays(15)->endOfDay();

echo "Today: " . $today->format('Y-m-d') . PHP_EOL;
echo "Threshold: " . $thresholdDate->format('Y-m-d') . PHP_EOL;

$expiringProducts = Product::whereNotNull('expiry_date')
    ->whereBetween('expiry_date', [$today, $thresholdDate])
    ->get();

echo "Found {$expiringProducts->count()} expiring products:" . PHP_EOL;

foreach ($expiringProducts as $product) {
    echo "- {$product->name} (Expiry: " . $product->expiry_date->format('d/m/Y') . ")" . PHP_EOL;
}

// Clear notifications and create new ones
DB::table('notifications')->delete();
echo PHP_EOL . "Cleared existing notifications." . PHP_EOL;

if ($expiringProducts->count() > 0) {
    $admins = User::all();
    
    foreach ($expiringProducts as $product) {
        foreach ($admins as $admin) {
            \Filament\Notifications\Notification::make()
                ->title('মেয়াদ শেষ হচ্ছে!')
                ->body("পণ্য: {$product->name}")
                ->warning()
                ->sendToDatabase($admin);
        }
    }
    
    echo "Created notifications for {$expiringProducts->count()} products." . PHP_EOL;
} else {
    echo "No expiring products found." . PHP_EOL;
}

// Verify notifications
$count = DB::table('notifications')->count();
echo PHP_EOL . "Total notifications in database: {$count}" . PHP_EOL;

// Test Laravel methods
$user = User::find(1);
if ($user) {
    $unreadCount = $user->unreadNotifications()->count();
    echo "Unread notifications for user: {$unreadCount}" . PHP_EOL;
    
    if ($unreadCount > 0) {
        echo PHP_EOL . "=== Sample Notifications ===" . PHP_EOL;
        $notifications = $user->unreadNotifications()->take(3)->get();
        foreach ($notifications as $notif) {
            $data = json_decode($notif->data, true);
            echo "Title: " . ($data['title'] ?? 'No title') . PHP_EOL;
            echo "Body: " . ($data['body'] ?? 'No body') . PHP_EOL;
            echo "---" . PHP_EOL;
        }
    }
}

echo PHP_EOL . "=== Ready for Testing ===" . PHP_EOL;
echo "1. Go to: http://127.0.0.1:8001/admin/login" . PHP_EOL;
echo "2. Login and check notification bell" . PHP_EOL;
