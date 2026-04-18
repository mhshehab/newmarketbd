<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix Notification System ===" . PHP_EOL;

use App\Models\User;

// Clear all existing notifications
DB::table('notifications')->delete();
echo "Cleared all existing notifications." . PHP_EOL;

// Get the user
$user = User::find(1);
if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

echo "User: {$user->name}" . PHP_EOL;

// Create a simple, Filament-compatible notification
$notificationData = [
    'title' => 'Product Expiry Warning',
    'body' => 'Potato Regular (± 50 gm) will expire on 26/04/2026',
    'icon' => 'heroicon-o-exclamation-triangle',
    'type' => 'warning',
    'actions' => [
        [
            'label' => 'View Product',
            'url' => '/admin/products/2/edit'
        ]
    ]
];

// Insert notification in the exact format Filament expects
$notificationId = \Illuminate\Support\Str::uuid();
DB::table('notifications')->insert([
    'id' => $notificationId,
    'type' => 'App\\Notifications\\ProductExpiryNotification',
    'notifiable_type' => 'App\\Models\\User',
    'notifiable_id' => $user->id,
    'data' => json_encode($notificationData),
    'read_at' => null,
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "Created new notification with ID: {$notificationId}" . PHP_EOL;

// Verify the notification was created correctly
$notification = DB::table('notifications')->where('id', $notificationId)->first();
if ($notification) {
    echo "Notification verified in database." . PHP_EOL;
    echo "Data: {$notification->data}" . PHP_EOL;
    
    // Test if user can access it
    $userNotification = $user->notifications()->where('id', $notificationId)->first();
    if ($userNotification) {
        echo "User can access the notification." . PHP_EOL;
    } else {
        echo "User CANNOT access the notification!" . PHP_EOL;
    }
} else {
    echo "Failed to create notification!" . PHP_EOL;
}

// Test unread notifications count
$unreadCount = $user->unreadNotifications()->count();
echo "Unread notifications count: {$unreadCount}" . PHP_EOL;

// Test the exact data format
echo PHP_EOL . "=== Testing Data Format ===" . PHP_EOL;
$data = json_decode($notification->data, true);
echo "Title: " . ($data['title'] ?? 'No title') . PHP_EOL;
echo "Body: " . ($data['body'] ?? 'No body') . PHP_EOL;
echo "Type: " . ($data['type'] ?? 'No type') . PHP_EOL;
echo "Icon: " . ($data['icon'] ?? 'No icon') . PHP_EOL;
