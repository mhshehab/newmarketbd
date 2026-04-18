<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fixing Notification Display Issues ===" . PHP_EOL;

use App\Models\User;

// Get the user
$user = User::find(1);

if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

// Fix notifications that don't have proper titles/bodies
$notifications = $user->notifications()->get();

echo "Found {$notifications->count()} notifications to check" . PHP_EOL;

$fixedCount = 0;
foreach ($notifications as $notification) {
    $data = $notification->data;
    
    // Check if data is string (JSON) or array
    if (is_string($data)) {
        $data = json_decode($data, true);
    }
    
    if (!is_array($data)) {
        echo "Skipping notification ID {$notification->id} - invalid data format" . PHP_EOL;
        continue;
    }
    
    $needsUpdate = false;
    
    // Fix missing title
    if (!isset($data['title']) || empty($data['title'])) {
        if (isset($data['message'])) {
            $data['title'] = $data['message'];
        } else {
            $data['title'] = 'Notification';
        }
        $needsUpdate = true;
    }
    
    // Fix missing body
    if (!isset($data['body']) || empty($data['body'])) {
        if (isset($data['product_name'])) {
            $data['body'] = 'Product: ' . $data['product_name'];
        } else {
            $data['body'] = 'You have a new notification';
        }
        $needsUpdate = true;
    }
    
    // Ensure type and icon are set
    if (!isset($data['type'])) {
        $data['type'] = 'info';
        $needsUpdate = true;
    }
    
    if (!isset($data['icon'])) {
        $data['icon'] = 'heroicon-o-bell';
        $needsUpdate = true;
    }
    
    if ($needsUpdate) {
        $notification->data = $data;
        $notification->save();
        $fixedCount++;
        echo "Fixed notification ID: {$notification->id}" . PHP_EOL;
    }
}

echo "Fixed {$fixedCount} notifications" . PHP_EOL;
