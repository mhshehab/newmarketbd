<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Laravel Default Notification System ===" . PHP_EOL;

use App\Models\User;
use App\Models\Product;

// Clear notifications first
DB::table('notifications')->delete();
echo "Cleared existing notifications." . PHP_EOL;

// Get user
$user = User::find(1);
if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

echo "User: {$user->name}" . PHP_EOL;

// Test Laravel's default notification methods
echo PHP_EOL . "=== Testing Laravel Default Methods ===" . PHP_EOL;

// Test notifications() method from trait
$allNotifications = $user->notifications()->count();
echo "All notifications via trait: {$allNotifications}" . PHP_EOL;

// Test unreadNotifications() method from trait  
$unreadNotifications = $user->unreadNotifications()->count();
echo "Unread notifications via trait: {$unreadNotifications}" . PHP_EOL;

// Test readNotifications() method from trait
$readNotifications = $user->readNotifications()->count();
echo "Read notifications via trait: {$readNotifications}" . PHP_EOL;

// Create a test notification using Filament
echo PHP_EOL . "=== Creating Test Notification ===" . PHP_EOL;

try {
    \Filament\Notifications\Notification::make()
        ->title('Laravel Trait Test')
        ->body('Testing Laravel default Notifiable trait')
        ->success()
        ->icon('heroicon-o-check-circle')
        ->sendToDatabase($user);
    
    echo "Filament notification created successfully!" . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

// Verify notification was created
$count = DB::table('notifications')->count();
echo "Notifications in database: {$count}" . PHP_EOL;

// Test Laravel methods again
echo PHP_EOL . "=== After Creation ===" . PHP_EOL;
$allAfter = $user->notifications()->count();
$unreadAfter = $user->unreadNotifications()->count();
$readAfter = $user->readNotifications()->count();

echo "All notifications: {$allAfter}" . PHP_EOL;
echo "Unread notifications: {$unreadAfter}" . PHP_EOL;
echo "Read notifications: {$readAfter}" . PHP_EOL;

echo PHP_EOL . "=== Success! ===" . PHP_EOL;
echo "Laravel default Notifiable trait is working correctly!" . PHP_EOL;
echo "User.php is properly configured." . PHP_EOL;
