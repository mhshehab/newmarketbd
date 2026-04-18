<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Manual Command Execution ===" . PHP_EOL;

// আজ থেকে ১৫ দিনের মধ্যে শেষ হবে এমন বা ইতিমধ্যেই শেষ হয়ে গেছে এমন পণ্য
$thresholdDate = now()->addDays(15)->endOfDay();
echo "Threshold date: " . $thresholdDate . PHP_EOL;

$expiringProducts = DB::table('products')
    ->whereNotNull('expiry_date')
    ->where('expiry_date', '<=', $thresholdDate)
    ->get();

echo "Found " . $expiringProducts->count() . " expiring products:" . PHP_EOL;

foreach ($expiringProducts as $product) {
    echo "- Product ID: {$product->id}, Name: {$product->name}, Expiry: {$product->expiry_date}" . PHP_EOL;
}

// সব অ্যাডমিন ইউজারকে খুঁজে বের করা
$admins = DB::table('users')->get();
echo "Found " . $admins->count() . " users:" . PHP_EOL;

foreach ($admins as $admin) {
    echo "- User ID: {$admin->id}, Name: {$admin->name}, Email: {$admin->email}" . PHP_EOL;
}

// এখন নোটিফিকেশন পাঠানোর চেষ্টা
echo PHP_EOL . "=== Sending Notifications ===" . PHP_EOL;

foreach ($expiringProducts as $product) {
    foreach ($admins as $admin) {
        echo "Sending notification to user {$admin->id} for product {$product->id}..." . PHP_EOL;
        
        try {
            // নোটিফিকেশন ডেটা তৈরি
            $notificationData = [
                'title' => 'পণ্যের মেয়াদ দ্রুত শেষ হচ্ছে!',
                'body' => "পণ্য: {$product->name} (মেয়াদ: " . \Carbon\Carbon::parse($product->expiry_date)->format('d/m/Y') . ")",
                'type' => 'warning',
                'icon' => 'heroicon-o-calendar'
            ];
            
            // নোটিফিকেশন ডাটাবেসে ইনসার্ট
            $notificationId = DB::table('notifications')->insertGetId([
                'id' => Str::uuid()->toString(),
                'type' => 'App\Notifications\ProductExpiryNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $admin->id,
                'data' => json_encode($notificationData),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            echo "  ✓ Notification inserted with ID: {$notificationId}" . PHP_EOL;
            
        } catch (Exception $e) {
            echo "  ✗ Error: " . $e->getMessage() . PHP_EOL;
        }
    }
}

echo PHP_EOL . "=== Final Check ===" . PHP_EOL;
$totalNotifications = DB::table('notifications')->count();
echo "Total notifications now: " . $totalNotifications . PHP_EOL;
