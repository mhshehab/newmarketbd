<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debugging Notification Data Format ===" . PHP_EOL;

use App\Models\User;

// Get the user
$user = User::find(1);

if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

// Get the latest notification
$notification = $user->notifications()->latest()->first();

if (!$notification) {
    echo "No notifications found!" . PHP_EOL;
    exit;
}

echo "Latest notification ID: {$notification->id}" . PHP_EOL;
echo "Data type: " . gettype($notification->data) . PHP_EOL;
echo "Raw data: ";
var_dump($notification->data);
echo PHP_EOL;

// If it's a string, decode it
if (is_string($notification->data)) {
    $decoded = json_decode($notification->data, true);
    echo "Decoded data: ";
    print_r($decoded);
} elseif (is_array($notification->data)) {
    echo "Array data: ";
    print_r($notification->data);
}

// Test the toArray method
echo "toArray() result: ";
print_r($notification->toArray());
