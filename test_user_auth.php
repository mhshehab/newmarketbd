<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== User Authentication Test ===" . PHP_EOL;

use App\Models\User;

// Get the current logged in user (if any)
// Since we're in CLI, we'll test with user ID 1
$user = User::find(1);

if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

echo "User found: " . $user->name . PHP_EOL;
echo "Email: " . $user->email . PHP_EOL;
echo "User ID: " . $user->id . PHP_EOL;

// Check if user has admin role (if roles system exists)
if (method_exists($user, 'roles')) {
    echo "User roles: " . $user->roles->pluck('name')->implode(', ') . PHP_EOL;
} else {
    echo "No roles system detected" . PHP_EOL;
}

// Check unread notifications
$unreadCount = $user->unreadNotifications()->count();
echo "Unread notifications count: " . $unreadCount . PHP_EOL;

// Check if user can access Filament admin
echo "Can access admin panel: " . ($user->email === 'mh.shehab@yahoo.com.au' ? 'Yes' : 'No') . PHP_EOL;

// Test Filament notification access
echo "Testing Filament notification access..." . PHP_EOL;

// Check if user has the required methods for Filament
$hasNotifications = method_exists($user, 'notifications');
$hasUnreadNotifications = method_exists($user, 'unreadNotifications');
$hasReadNotifications = method_exists($user, 'readNotifications');

echo "Has notifications method: " . ($hasNotifications ? 'Yes' : 'No') . PHP_EOL;
echo "Has unreadNotifications method: " . ($hasUnreadNotifications ? 'Yes' : 'No') . PHP_EOL;
echo "Has readNotifications method: " . ($hasReadNotifications ? 'Yes' : 'No') . PHP_EOL;

// Show latest 3 notifications
echo PHP_EOL . "Latest 3 notifications:" . PHP_EOL;
$notifications = $user->notifications()->latest()->limit(3)->get();

foreach ($notifications as $notification) {
    $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);
    echo "- Title: " . ($data['title'] ?? 'No title') . PHP_EOL;
    echo "  Body: " . ($data['body'] ?? 'No body') . PHP_EOL;
    echo "  Read: " . ($notification->read_at ? 'Yes' : 'No') . PHP_EOL;
    echo "  ---" . PHP_EOL;
}
