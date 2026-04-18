<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Final Notification Test ===" . PHP_EOL;

// Check current notifications
$count = DB::table('notifications')->count();
echo "Current notifications in database: {$count}" . PHP_EOL;

if ($count > 0) {
    echo PHP_EOL . "Existing notifications:" . PHP_EOL;
    $notifications = DB::table('notifications')->get();
    foreach ($notifications as $notif) {
        $data = json_decode($notif->data, true);
        echo "- ID: {$notif->id}" . PHP_EOL;
        echo "  Title: " . ($data['title'] ?? 'No title') . PHP_EOL;
        echo "  User ID: {$notif->notifiable_id}" . PHP_EOL;
        echo "  Read: " . ($notif->read_at ? 'Yes' : 'No') . PHP_EOL;
        echo "---" . PHP_EOL;
    }
} else {
    echo "No notifications found. Creating test notification..." . PHP_EOL;
    
    // Create a test notification for user ID 1
    $notificationId = \Illuminate\Support\Str::uuid();
    DB::table('notifications')->insert([
        'id' => $notificationId,
        'type' => 'App\\Notifications\\TestNotification',
        'notifiable_type' => 'App\\Models\\User',
        'notifiable_id' => 1,
        'data' => json_encode([
            'title' => 'Final Test Notification',
            'body' => 'This is the final test notification',
            'icon' => 'heroicon-o-bell',
            'type' => 'info'
        ]),
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "Test notification created with ID: {$notificationId}" . PHP_EOL;
}

echo PHP_EOL . "=== Configuration Summary ===" . PHP_EOL;
echo "AdminPanelProvider.php:" . PHP_EOL;
echo "- databaseNotifications(): ENABLED" . PHP_EOL;
echo "- databaseNotificationsPolling('30s'): ENABLED" . PHP_EOL;
echo "- User model: Has Notifiable trait" . PHP_EOL;
echo "- Database: notifications table exists" . PHP_EOL;

echo PHP_EOL . "=== Next Steps ===" . PHP_EOL;
echo "1. Go to: http://127.0.0.1:8001/admin/login" . PHP_EOL;
echo "2. Login with your credentials" . PHP_EOL;
echo "3. Check notification bell in top-right corner" . PHP_EOL;
echo "4. If still no notifications, wait 30 seconds for polling" . PHP_EOL;
