<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Filament Notification Debug ===" . PHP_EOL;

use App\Models\User;

// Get the user
$user = User::find(1);

if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

echo "User: {$user->name} (ID: {$user->id})" . PHP_EOL;

// Test the exact query Filament might use
echo PHP_EOL . "=== Testing Filament-style queries ===" . PHP_EOL;

// Test unread notifications query
$unreadNotifications = $user->unreadNotifications()->get();
echo "Unread notifications count: " . $unreadNotifications->count() . PHP_EOL;

// Test with limit (Filament usually limits notifications)
$limitedNotifications = $user->unreadNotifications()->limit(10)->get();
echo "Limited notifications count: " . $limitedNotifications->count() . PHP_EOL;

// Test if notifications are properly formatted
echo PHP_EOL . "=== Testing notification format ===" . PHP_EOL;
foreach ($limitedNotifications as $notification) {
    $data = json_decode($notification->data, true);
    echo "Notification ID: {$notification->id}" . PHP_EOL;
    echo "Title: " . ($data['title'] ?? 'No title') . PHP_EOL;
    echo "Body: " . ($data['body'] ?? 'No body') . PHP_EOL;
    echo "Type: " . ($data['type'] ?? 'No type') . PHP_EOL;
    echo "Read at: " . ($notification->read_at ?? 'Not read') . PHP_EOL;
    echo "---" . PHP_EOL;
}

// Test Filament's specific notification format
echo PHP_EOL . "=== Testing Filament notification format ===" . PHP_EOL;

// Create a Filament-style notification
$filamentNotification = [
    'id' => (string)\Illuminate\Support\Str::uuid(),
    'title' => 'Filament Test Notification',
    'body' => 'This is a test for Filament',
    'actions' => [
        [
            'label' => 'Test Action',
            'url' => '/admin'
        ]
    ],
    'icon' => 'heroicon-o-bell',
    'type' => 'info'
];

// Insert it directly
DB::table('notifications')->insert([
    'id' => $filamentNotification['id'],
    'type' => 'Filament\\Notifications\\DatabaseNotification',
    'notifiable_type' => 'App\\Models\\User',
    'notifiable_id' => $user->id,
    'data' => json_encode($filamentNotification),
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "Filament-style notification created!" . PHP_EOL;

// Check total notifications again
$totalCount = DB::table('notifications')->count();
echo "Total notifications now: {$totalCount}" . PHP_EOL;

// Test if the user can see it
$userNotifications = $user->notifications()->where('id', $filamentNotification['id'])->first();
if ($userNotifications) {
    echo "Filament notification found for user!" . PHP_EOL;
} else {
    echo "Filament notification NOT found for user!" . PHP_EOL;
}
