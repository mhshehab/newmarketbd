<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Simple Notification ===" . PHP_EOL;

use App\Models\User;

// ইউজার খুঁজে বের করা
$user = User::find(1);

if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

echo "Found user: " . $user->name . PHP_EOL;

// সাধারণ নোটিফিকেশন তৈরি করা
$notification = new \Illuminate\Notifications\DatabaseNotification([
    'id' => \Illuminate\Support\Str::uuid(),
    'type' => 'App\\Notifications\\SimpleTestNotification',
    'notifiable_type' => get_class($user),
    'notifiable_id' => $user->id,
    'data' => json_encode([
        'title' => 'Test Notification',
        'body' => 'This is a simple test notification',
        'type' => 'info'
    ]),
    'created_at' => now(),
    'updated_at' => now(),
]);

// নোটিফিকেশন ডাটাবেসে সেভ করা
$user->notifications()->save($notification);

echo "Simple notification created successfully!" . PHP_EOL;

// ইউজারের নোটিফিকেশন চেক করা
$notifications = $user->notifications()->get();
echo "User has " . $notifications->count() . " notifications:" . PHP_EOL;

foreach ($notifications as $notif) {
    echo "- ID: " . $notif->id . ", Title: " . json_decode($notif->data)->title . PHP_EOL;
}

// Unread notifications চেক করা
$unreadNotifications = $user->unreadNotifications;
echo "Unread notifications: " . $unreadNotifications->count() . PHP_EOL;
