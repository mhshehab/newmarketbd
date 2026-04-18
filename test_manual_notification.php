<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Manual Notification Test ===" . PHP_EOL;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

// Get user
$user = User::find(1);
if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

echo "User: {$user->name}" . PHP_EOL;

// Clear notifications
DB::table('notifications')->delete();
echo "Cleared notifications." . PHP_EOL;

// Create notification manually using Laravel's DatabaseNotification
$notification = new DatabaseNotification([
    'id' => \Illuminate\Support\Str::uuid(),
    'type' => 'Filament\\Notifications\\DatabaseNotification',
    'notifiable_type' => 'App\\Models\\User',
    'notifiable_id' => $user->id,
    'data' => [
        'title' => 'Test Notification',
        'body' => 'This is a manual test',
        'icon' => 'heroicon-o-bell',
        'type' => 'info'
    ],
    'read_at' => null,
    'created_at' => now(),
    'updated_at' => now(),
]);

// Save notification
$user->notifications()->save($notification);
echo "Manual notification created!" . PHP_EOL;

// Verify
$count = DB::table('notifications')->count();
echo "Notifications in database: {$count}" . PHP_EOL;

if ($count > 0) {
    $notif = DB::table('notifications')->first();
    echo "Notification ID: {$notif->id}" . PHP_EOL;
    echo "Data: " . json_encode($notif->data) . PHP_EOL;
}

// Test Laravel's default methods
echo PHP_EOL . "=== Test Laravel Methods ===" . PHP_EOL;
$unreadCount = $user->unreadNotifications()->count();
echo "Unread notifications: {$unreadCount}" . PHP_EOL;

$allCount = $user->notifications()->count();
echo "All notifications: {$allCount}" . PHP_EOL;
