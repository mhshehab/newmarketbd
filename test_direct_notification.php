<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Direct Notification Test ===" . PHP_EOL;

use App\Models\User;

// Get the user
$user = User::find(1);

if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

echo "User: " . $user->name . " (ID: " . $user->id . ")" . PHP_EOL;

// Check if user can receive notifications
echo "User uses Notifiable trait: " . (method_exists($user, 'notify') ? 'Yes' : 'No') . PHP_EOL;

// Create a simple notification class
class TestNotification extends \Illuminate\Notifications\Notification
{
    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Direct Test Notification',
            'body' => 'This is a direct test notification from script',
            'type' => 'success',
            'icon' => 'heroicon-o-check-circle'
        ];
    }
}

// Send notification
try {
    $user->notify(new TestNotification());
    echo "Notification sent successfully!" . PHP_EOL;
} catch (Exception $e) {
    echo "Error sending notification: " . $e->getMessage() . PHP_EOL;
}

// Check notifications count
$allNotifications = $user->notifications()->count();
$unreadNotifications = $user->unreadNotifications()->count();

echo "Total notifications: " . $allNotifications . PHP_EOL;
echo "Unread notifications: " . $unreadNotifications . PHP_EOL;

// Show latest notification
$latestNotification = $user->notifications()->latest()->first();
if ($latestNotification) {
    echo "Latest notification data: " . $latestNotification->data . PHP_EOL;
    echo "Read at: " . ($latestNotification->read_at ?? 'Not read') . PHP_EOL;
}
