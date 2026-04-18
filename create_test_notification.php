<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Create Test Notification ===" . PHP_EOL;

use App\Models\User;

// Clear existing notifications
DB::table('notifications')->delete();
echo "Cleared existing notifications." . PHP_EOL;

// Get user
$user = User::find(1);
if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

echo "User: {$user->name}" . PHP_EOL;

// Create simple test notification
class TestNotification extends \Illuminate\Notifications\Notification
{
    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Test Notification',
            'body' => 'This is a simple test notification',
            'icon' => 'heroicon-o-bell',
            'type' => 'info'
        ];
    }
}

// Send notification
$user->notify(new TestNotification());
echo "Test notification sent!" . PHP_EOL;

// Verify
$count = $user->unreadNotifications()->count();
echo "Unread notifications: {$count}" . PHP_EOL;

// Show notification
$notification = $user->unreadNotifications()->first();
if ($notification) {
    echo "Notification title: " . $notification->data['title'] . PHP_EOL;
    echo "Notification body: " . $notification->data['body'] . PHP_EOL;
}
