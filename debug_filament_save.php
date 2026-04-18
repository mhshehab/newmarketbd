<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Filament Notification Save Issue ===" . PHP_EOL;

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

// Test 1: Try manual DatabaseNotification creation
echo PHP_EOL . "=== Test 1: Manual DatabaseNotification ===" . PHP_EOL;

try {
    $notification = new \Illuminate\Notifications\DatabaseNotification([
        'id' => \Illuminate\Support\Str::uuid(),
        'type' => 'Filament\\Notifications\\DatabaseNotification',
        'notifiable_type' => 'App\\Models\\User',
        'notifiable_id' => $user->id,
        'data' => json_encode([
            'title' => 'Manual Test',
            'body' => 'Manual database notification',
            'icon' => 'heroicon-o-bell',
            'type' => 'info'
        ]),
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $user->notifications()->save($notification);
    echo "Manual notification saved!" . PHP_EOL;
} catch (Exception $e) {
    echo "Manual error: " . $e->getMessage() . PHP_EOL;
}

// Check count
$count1 = DB::table('notifications')->count();
echo "Notifications after manual: {$count1}" . PHP_EOL;

// Test 2: Try Filament Notification
echo PHP_EOL . "=== Test 2: Filament Notification ===" . PHP_EOL;

try {
    \Filament\Notifications\Notification::make()
        ->title('Filament Test')
        ->body('Filament notification test')
        ->warning()
        ->icon('heroicon-o-exclamation-triangle')
        ->sendToDatabase($user);
    
    echo "Filament notification sent!" . PHP_EOL;
} catch (Exception $e) {
    echo "Filament error: " . $e->getMessage() . PHP_EOL;
}

// Check count
$count2 = DB::table('notifications')->count();
echo "Notifications after Filament: {$count2}" . PHP_EOL;

// Test 3: Check if sendToDatabase method exists
echo PHP_EOL . "=== Test 3: Method Check ===" . PHP_EOL;

if (method_exists(\Filament\Notifications\Notification::class, 'sendToDatabase')) {
    echo "sendToDatabase method exists!" . PHP_EOL;
} else {
    echo "sendToDatabase method NOT found!" . PHP_EOL;
}

// Show all notifications
echo PHP_EOL . "=== All Notifications ===" . PHP_EOL;
$notifications = DB::table('notifications')->get();
foreach ($notifications as $notif) {
    echo "ID: {$notif->id}" . PHP_EOL;
    echo "Type: {$notif->type}" . PHP_EOL;
    echo "Data: {$notif->data}" . PHP_EOL;
    echo "---" . PHP_EOL;
}
