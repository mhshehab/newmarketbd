<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Step by Step Notification Debug ===" . PHP_EOL;

use App\Models\User;
use App\Models\Product;

// Step 1: Clear notifications
DB::table('notifications')->delete();
echo "Step 1: Cleared all notifications." . PHP_EOL;

// Step 2: Get user
$user = User::find(1);
if (!$user) {
    echo "Step 2: User not found!" . PHP_EOL;
    exit;
}
echo "Step 2: User found: {$user->name}" . PHP_EOL;

// Step 3: Get expiring products
$today = now()->startOfDay();
$thresholdDate = now()->addDays(15)->endOfDay();

$expiringProducts = Product::whereNotNull('expiry_date')
    ->whereBetween('expiry_date', [$today, $thresholdDate])
    ->get();

echo "Step 3: Found {$expiringProducts->count()} expiring products." . PHP_EOL;

if ($expiringProducts->count() > 0) {
    $product = $expiringProducts->first();
    echo "Step 4: Creating notification for: {$product->name}" . PHP_EOL;
    
    // Step 4: Create notification
    try {
        $notification = new \Illuminate\Notifications\DatabaseNotification([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\ProductExpiryNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'title' => 'মেয়াদ শেষ হচ্ছে!',
                'body' => "পণ্য: {$product->name}",
                'icon' => 'heroicon-o-calendar',
                'type' => 'warning',
                'actions' => [
                    [
                        'label' => 'মেয়াদ ঠিক করুন',
                        'url' => "/admin/products/{$product->id}/edit",
                        'color' => 'primary'
                    ]
                ]
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $user->notifications()->save($notification);
        echo "Step 5: Notification object created" . PHP_EOL;
        
        // Step 5: Verify in database
        $count = DB::table('notifications')->count();
        echo "Step 6: Notifications in database: {$count}" . PHP_EOL;
        
        if ($count > 0) {
            $notif = DB::table('notifications')->first();
            echo "Step 7: Notification details:" . PHP_EOL;
            echo "   ID: {$notif->id}" . PHP_EOL;
            echo "   Type: {$notif->type}" . PHP_EOL;
            echo "   Data: {$notif->data}" . PHP_EOL;
            
            // Step 6: Test Laravel methods
            $unreadCount = $user->unreadNotifications()->count();
            echo "Step 8: Unread notifications: {$unreadCount}" . PHP_EOL;
            
            if ($unreadCount > 0) {
                echo "Step 9: SUCCESS - Notification system working!" . PHP_EOL;
                echo PHP_EOL . "=== Next Steps ===" . PHP_EOL;
                echo "1. Go to: http://127.0.0.1:8001/admin/login" . PHP_EOL;
                echo "2. Login and check notification bell" . PHP_EOL;
                echo "3. Click notification to expand" . PHP_EOL;
                echo "4. Click action button" . PHP_EOL;
                echo "5. If still not visible, check browser console" . PHP_EOL;
            } else {
                echo "Step 9: ERROR - Laravel methods not working!" . PHP_EOL;
            }
        } else {
            echo "Step 7: ERROR - Notification not saved to database!" . PHP_EOL;
        }
        
    } catch (Exception $e) {
        echo "Step 5: ERROR - " . $e->getMessage() . PHP_EOL;
    }
} else {
    echo "Step 4: No expiring products found." . PHP_EOL;
}

echo PHP_EOL . "=== Debug Complete ===" . PHP_EOL;
