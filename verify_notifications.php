<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verify Notifications ===" . PHP_EOL;

// Check notifications in database
$notifications = DB::table('notifications')->get();
echo "Total notifications: " . $notifications->count() . PHP_EOL;

foreach ($notifications as $notification) {
    echo PHP_EOL . "Notification ID: {$notification->id}" . PHP_EOL;
    echo "Type: {$notification->type}" . PHP_EOL;
    echo "Notifiable Type: {$notification->notifiable_type}" . PHP_EOL;
    echo "Notifiable ID: {$notification->notifiable_id}" . PHP_EOL;
    echo "Data: {$notification->data}" . PHP_EOL;
    echo "Read At: " . ($notification->read_at ?? 'Unread') . PHP_EOL;
    echo "Created At: {$notification->created_at}" . PHP_EOL;
    echo "---" . PHP_EOL;
}

// Test if Laravel's default Notifiable trait works
echo PHP_EOL . "=== Test Laravel Default Notifiable ===" . PHP_EOL;
use App\Models\User;

$user = User::find(1);
if ($user) {
    echo "User found: {$user->name}" . PHP_EOL;
    $unreadNotifications = $user->unreadNotifications()->count();
    echo "Unread notifications via Laravel: {$unreadNotifications}" . PHP_EOL;
    
    // Test if notifications() method works (from trait)
    $allNotifications = $user->notifications()->count();
    echo "All notifications via Laravel: {$allNotifications}" . PHP_EOL;
} else {
    echo "User not found!" . PHP_EOL;
}

echo PHP_EOL . "=== Ready for Testing ===" . PHP_EOL;
echo "1. Go to: http://127.0.0.1:8001/admin/login" . PHP_EOL;
echo "2. Login with your credentials" . PHP_EOL;
echo "3. Check notification bell in top-right corner" . PHP_EOL;
echo "4. You should see 'Product Expiry Warning' notifications" . PHP_EOL;
