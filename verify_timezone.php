<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verify Timezone Configuration ===" . PHP_EOL;

// Check current timezone
echo "Current timezone: " . date_default_timezone_get() . PHP_EOL;
echo "App timezone: " . config('app.timezone') . PHP_EOL;

// Test current time in Bangladesh
$now = now();
echo "Current time: " . $now->format('Y-m-d H:i:s') . PHP_EOL;
echo "Bangladesh time: " . $now->timezone('Asia/Dhaka')->format('Y-m-d H:i:s') . PHP_EOL;

// Check if timezone is correctly set
if (config('app.timezone') === 'Asia/Dhaka') {
    echo "Timezone correctly set to Asia/Dhaka!" . PHP_EOL;
} else {
    echo "Timezone not properly configured." . PHP_EOL;
}

// Test notification creation with timezone
echo PHP_EOL . "=== Test Notification with Timezone ===" . PHP_EOL;

use App\Models\User;

$user = User::find(1);
if ($user) {
    echo "User: {$user->name}" . PHP_EOL;
    
    // Create a test notification
    try {
        \Filament\Notifications\Notification::make()
            ->title('Timezone Test Notification')
            ->body('Testing with Bangladesh timezone: ' . now()->format('d/m/Y H:i'))
            ->info()
            ->icon('heroicon-o-clock')
            ->sendToDatabase($user);
        
        echo "Notification created with Bangladesh timezone!" . PHP_EOL;
        
        // Check notification
        $count = DB::table('notifications')->count();
        echo "Total notifications: {$count}" . PHP_EOL;
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . PHP_EOL;
    }
}
