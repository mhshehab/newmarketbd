<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Notifications Count ===" . PHP_EOL;

// Check notifications count
$count = DB::table('notifications')->count();
echo "Total notifications in database: {$count}" . PHP_EOL;

if ($count > 0) {
    echo PHP_EOL . "=== Notification Details ===" . PHP_EOL;
    $notifications = DB::table('notifications')->get();
    
    foreach ($notifications as $notif) {
        echo "ID: {$notif->id}" . PHP_EOL;
        echo "Type: {$notif->type}" . PHP_EOL;
        echo "Notifiable ID: {$notif->notifiable_id}" . PHP_EOL;
        echo "Notifiable Type: {$notif->notifiable_type}" . PHP_EOL;
        echo "Read At: " . ($notif->read_at ?? 'Unread') . PHP_EOL;
        echo "Created At: {$notif->created_at}" . PHP_EOL;
        
        $data = json_decode($notif->data, true);
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
        echo "---" . PHP_EOL;
    }
    
    // Test Laravel methods
    echo PHP_EOL . "=== Laravel Methods Test ===" . PHP_EOL;
    use App\Models\User;
    
    $user = User::find(1);
    if ($user) {
        $unreadCount = $user->unreadNotifications()->count();
        $allCount = $user->notifications()->count();
        
        echo "Unread notifications: {$unreadCount}" . PHP_EOL;
        echo "All notifications: {$allCount}" . PHP_EOL;
        
        if ($unreadCount > 0) {
            echo PHP_EOL . "=== Ready for Filament Panel ===" . PHP_EOL;
            echo "1. Go to: http://127.0.0.1:8001/admin/login" . PHP_EOL;
            echo "2. Login and check notification bell" . PHP_EOL;
            echo "3. Notifications should appear!" . PHP_EOL;
        }
    }
} else {
    echo "No notifications found in database." . PHP_EOL;
    echo PHP_EOL . "=== Run Command to Create Notifications ===" . PHP_EOL;
    echo "php artisan app:check-product-expiry" . PHP_EOL;
}

echo PHP_EOL . "=== Verification Complete ===" . PHP_EOL;
