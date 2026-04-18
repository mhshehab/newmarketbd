<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Simple Notification Check ===" . PHP_EOL;

use App\Models\User;

// Get the user
$user = User::find(1);

if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

echo "User: {$user->name} (ID: {$user->id})" . PHP_EOL;

// Get all notifications
$notifications = $user->notifications()->latest()->get();
echo "Total notifications: " . $notifications->count() . PHP_EOL;

// Get unread notifications
$unreadNotifications = $user->unreadNotifications()->get();
echo "Unread notifications: " . $unreadNotifications->count() . PHP_EOL;

// Show notification details
echo PHP_EOL . "=== Notification Details ===" . PHP_EOL;
foreach ($notifications->take(3) as $notification) {
    echo "ID: {$notification->id}" . PHP_EOL;
    echo "Read: " . ($notification->read_at ? 'Yes (' . $notification->read_at . ')' : 'No') . PHP_EOL;
    echo "Type: {$notification->type}" . PHP_EOL;
    echo "Data: " . json_encode($notification->data, JSON_PRETTY_PRINT) . PHP_EOL;
    echo "Created: {$notification->created_at}" . PHP_EOL;
    echo "---" . PHP_EOL;
}
