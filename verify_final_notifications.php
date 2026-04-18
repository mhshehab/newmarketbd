<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Final Notification Verification ===" . PHP_EOL;

// Check notifications in database
$notifications = DB::table('notifications')->get();
echo "Total notifications: " . $notifications->count() . PHP_EOL;

foreach ($notifications as $notification) {
    echo PHP_EOL . "Notification:" . PHP_EOL;
    echo "ID: {$notification->id}" . PHP_EOL;
    echo "Type: {$notification->type}" . PHP_EOL;
    echo "Data: {$notification->data}" . PHP_EOL;
    echo "Created: {$notification->created_at}" . PHP_EOL;
    
    // Decode and show structured data
    $data = json_decode($notification->data, true);
    if ($data) {
        echo "Title: " . ($data['title'] ?? 'No title') . PHP_EOL;
        echo "Body: " . ($data['body'] ?? 'No body') . PHP_EOL;
        echo "Icon: " . ($data['icon'] ?? 'No icon') . PHP_EOL;
        echo "Type: " . ($data['type'] ?? 'No type') . PHP_EOL;
    }
    echo "---" . PHP_EOL;
}

// Test Laravel methods
echo PHP_EOL . "=== Test Laravel Methods ===" . PHP_EOL;
use App\Models\User;

$user = User::find(1);
if ($user) {
    echo "User: {$user->name}" . PHP_EOL;
    
    $unreadCount = $user->unreadNotifications()->count();
    $allCount = $user->notifications()->count();
    
    echo "Unread notifications: {$unreadCount}" . PHP_EOL;
    echo "All notifications: {$allCount}" . PHP_EOL;
}

echo PHP_EOL . "=== Ready for Filament Panel ===" . PHP_EOL;
echo "1. Go to: http://127.0.0.1:8001/admin/login" . PHP_EOL;
echo "2. Login with your credentials" . PHP_EOL;
echo "3. Check notification bell (top-right corner)" . PHP_EOL;
echo "4. Notifications should appear now!" . PHP_EOL;
