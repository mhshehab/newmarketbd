<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Notification Creation ===" . PHP_EOL;

use App\Models\Product;
use App\Models\User;

// Test Filament notification creation directly
echo "Testing direct Filament notification creation..." . PHP_EOL;

// Get a test user
$user = User::find(1);
if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

echo "User: {$user->name}" . PHP_EOL;

// Clear notifications first
DB::table('notifications')->delete();
echo "Cleared existing notifications." . PHP_EOL;

// Create a test notification using Filament format
try {
    \Filament\Notifications\Notification::make()
        ->title('Test Notification')
        ->body('This is a test notification')
        ->warning()
        ->icon('heroicon-o-bell')
        ->sendToDatabase($user);
    
    echo "Filament notification created successfully!" . PHP_EOL;
} catch (Exception $e) {
    echo "Error creating Filament notification: " . $e->getMessage() . PHP_EOL;
}

// Check if notification was created
$count = DB::table('notifications')->count();
echo "Notifications in database: {$count}" . PHP_EOL;

if ($count > 0) {
    $notification = DB::table('notifications')->first();
    echo "Notification data: {$notification->data}" . PHP_EOL;
    echo "Notification type: {$notification->type}" . PHP_EOL;
    echo "Notifiable ID: {$notification->notifiable_id}" . PHP_EOL;
    echo "Notifiable Type: {$notification->notifiable_type}" . PHP_EOL;
}
