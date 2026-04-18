<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Manual sendToDatabase Test ===" . PHP_EOL;

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
    'data' => json_encode([
        'title' => 'Manual Test',
        'body' => 'Testing manual creation',
        'icon' => 'heroicon-o-bell',
        'type' => 'info'
    ]),
    'read_at' => null,
    'created_at' => now(),
    'updated_at' => now(),
]);

// Save notification
$user->notifications()->save($notification);
echo "Manual notification created and saved!" . PHP_EOL;

// Check database
$count = DB::table('notifications')->count();
echo "Notifications in database: {$count}" . PHP_EOL;

// Test Laravel methods
$unreadCount = $user->unreadNotifications()->count();
echo "Unread notifications: {$unreadCount}" . PHP_EOL;

// Show notification details
if ($count > 0) {
    $notif = DB::table('notifications')->first();
    echo "Notification details:" . PHP_EOL;
    echo "ID: {$notif->id}" . PHP_EOL;
    echo "Type: {$notif->type}" . PHP_EOL;
    echo "Data: {$notif->data}" . PHP_EOL;
    
    // Test if Filament can read this
    $data = json_decode($notif->data, true);
    if ($data && isset($data['title'])) {
        echo "Title found: " . $data['title'] . PHP_EOL;
        echo "Body found: " . $data['body'] . PHP_EOL;
        echo "This format should work with Filament!" . PHP_EOL;
    }
}
