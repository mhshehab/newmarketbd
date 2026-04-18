<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Creating New Test Notification ===" . PHP_EOL;

use App\Models\User;
use Illuminate\Support\Facades\Notification;

// Get the user
$user = User::find(1);

if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

echo "Creating notification for user: {$user->name}" . PHP_EOL;

// Create a simple test notification
$notification = new class extends \Illuminate\Notifications\Notification {
    public function via($notifiable) {
        return ['database'];
    }
    
    public function toArray($notifiable) {
        return [
            'title' => 'Test Notification',
            'body' => 'This is a test notification created at ' . now()->format('H:i:s'),
            'type' => 'info',
            'icon' => 'heroicon-o-bell',
        ];
    }
};

// Send the notification
$user->notify($notification);

echo "Notification sent!" . PHP_EOL;

// Check the count
echo "Total notifications now: " . $user->notifications()->count() . PHP_EOL;
echo "Unread notifications now: " . $user->unreadNotifications()->count() . PHP_EOL;
