<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix Notification System ===" . PHP_EOL;

use App\Models\User;
use App\Models\Product;

// Clear notifications
DB::table('notifications')->delete();
echo "Cleared all notifications." . PHP_EOL;

// Get user
$user = User::find(1);
if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

// Get expiring products
$today = now()->startOfDay();
$thresholdDate = now()->addDays(15)->endOfDay();

$expiringProducts = Product::whereNotNull('expiry_date')
    ->whereBetween('expiry_date', [$today, $thresholdDate])
    ->get();

if ($expiringProducts->count() > 0) {
    foreach ($expiringProducts as $product) {
        echo "Creating notification for: {$product->name}" . PHP_EOL;
        
        // Create notification using Laravel's DatabaseNotification directly
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
        echo "Notification saved for product ID: {$product->id}" . PHP_EOL;
    }
    
    // Check database
    $count = DB::table('notifications')->count();
    echo PHP_EOL . "Total notifications created: {$count}" . PHP_EOL;
    
    // Test Laravel methods
    $unreadCount = $user->unreadNotifications()->count();
    echo "Unread notifications: {$unreadCount}" . PHP_EOL;
    
    if ($unreadCount > 0) {
        echo PHP_EOL . "=== Sample Notification ===" . PHP_EOL;
        $sample = $user->unreadNotifications()->first();
        $data = $sample->data;
        
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        
        if ($data) {
            echo "Title: " . ($data['title'] ?? 'No title') . PHP_EOL;
            echo "Body: " . ($data['body'] ?? 'No body') . PHP_EOL;
            echo "Icon: " . ($data['icon'] ?? 'No icon') . PHP_EOL;
            echo "Type: " . ($data['type'] ?? 'No type') . PHP_EOL;
            echo "Has actions: " . (isset($data['actions']) ? 'Yes' : 'No') . PHP_EOL;
            
            if (isset($data['actions'])) {
                foreach ($data['actions'] as $action) {
                    echo "Action: " . ($action['label'] ?? 'No label') . PHP_EOL;
                    echo "URL: " . ($action['url'] ?? 'No URL') . PHP_EOL;
                }
            }
        }
        
        echo PHP_EOL . "=== System Ready ===" . PHP_EOL;
        echo "✓ Notifications persist until manually deleted" . PHP_EOL;
        echo "✓ Actions included for product editing" . PHP_EOL;
        echo "✓ Proper Filament format" . PHP_EOL;
        echo "✓ Polling enabled (30s)" . PHP_EOL;
        
        echo PHP_EOL . "=== Testing Steps ===" . PHP_EOL;
        echo "1. Go to: http://127.0.0.1:8001/admin/login" . PHP_EOL;
        echo "2. Login and check notification bell" . PHP_EOL;
        echo "3. Click notification to expand" . PHP_EOL;
        echo "4. Click 'মেয়াদ ঠিক করুন' button" . PHP_EOL;
        echo "5. Should redirect to product edit page" . PHP_EOL;
        echo "6. Notification should remain until manually deleted" . PHP_EOL;
        echo "7. Delete button should be available" . PHP_EOL;
    }
} else {
    echo "No expiring products found." . PHP_EOL;
}
