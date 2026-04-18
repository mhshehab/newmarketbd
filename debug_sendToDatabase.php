<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug sendToDatabase Method ===" . PHP_EOL;

use App\Models\User;

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

// Test 1: Check if sendToDatabase method exists
echo PHP_EOL . "=== Check sendToDatabase Method ===" . PHP_EOL;

$filamentNotificationClass = 'Filament\\Notifications\\Notification';
if (class_exists($filamentNotificationClass)) {
    echo "✓ Filament\\Notifications\\Notification class exists" . PHP_EOL;
    
    $methods = get_class_methods($filamentNotificationClass);
    if (in_array('sendToDatabase', $methods)) {
        echo "✓ sendToDatabase method exists" . PHP_EOL;
    } else {
        echo "✗ sendToDatabase method NOT found" . PHP_EOL;
        echo "Available methods: " . implode(', ', array_slice($methods, 0, 10)) . PHP_EOL;
    }
} else {
    echo "✗ Filament\\Notifications\\Notification class NOT found" . PHP_EOL;
}

// Test 2: Try to call sendToDatabase directly
echo PHP_EOL . "=== Test Direct sendToDatabase Call ===" . PHP_EOL;

try {
    $notification = \Filament\Notifications\Notification::make()
        ->title('Direct Test')
        ->body('Testing sendToDatabase directly')
        ->info()
        ->icon('heroicon-o-bell');
    
    echo "✓ Notification object created" . PHP_EOL;
    
    // Check if sendToDatabase method exists on the object
    if (method_exists($notification, 'sendToDatabase')) {
        echo "✓ sendToDatabase method exists on object" . PHP_EOL;
        $notification->sendToDatabase($user);
        echo "✓ sendToDatabase called successfully" . PHP_EOL;
    } else {
        echo "✗ sendToDatabase method NOT found on object" . PHP_EOL;
        echo "Available methods: " . implode(', ', array_slice(get_class_methods($notification), 0, 10)) . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
}

// Check database
$count = DB::table('notifications')->count();
echo PHP_EOL . "Notifications in database: {$count}" . PHP_EOL;
