<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Notification Save Issue ===" . PHP_EOL;

use App\Models\User;

// Clear notifications
DB::table('notifications')->delete();
echo "Cleared notifications." . PHP_EOL;

// Get user
$user = User::find(1);
if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

echo "User: {$user->name}" . PHP_EOL;

// Test 1: Manual DatabaseNotification creation
echo PHP_EOL . "=== Test 1: Manual DatabaseNotification ===" . PHP_EOL;

try {
    $notification = new \Illuminate\Notifications\DatabaseNotification([
        'id' => \Illuminate\Support\Str::uuid(),
        'type' => 'Filament\\Notifications\\DatabaseNotification',
        'notifiable_type' => 'App\\Models\\User',
        'notifiable_id' => $user->id,
        'data' => json_encode([
            'title' => 'Manual Test',
            'body' => 'Testing manual creation',
            'icon' => 'heroicon-o-bell',
            'type' => 'info',
            'actions' => [
                [
                    'label' => 'View Product',
                    'url' => '/admin/products/1/edit',
                    'color' => 'primary'
                ]
            ]
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

// Test 2: Filament notification
echo PHP_EOL . "=== Test 2: Filament Notification ===" . PHP_EOL;

try {
    \Filament\Notifications\Notification::make()
        ->title('Filament Test')
        ->body('Filament notification test')
        ->warning()
        ->icon('heroicon-o-calendar')
        ->actions([
            \Filament\Notifications\Actions\Action::make('view')
                ->label('Fix Product')
                ->url('/admin/products/1/edit')
                ->color('primary'),
        ])
        ->sendToDatabase($user);
    
    echo "Filament notification sent!" . PHP_EOL;
} catch (Exception $e) {
    echo "Filament error: " . $e->getMessage() . PHP_EOL;
}

// Check count
$count2 = DB::table('notifications')->count();
echo "Notifications after Filament: {$count2}" . PHP_EOL;

// Show all notifications
echo PHP_EOL . "=== All Notifications ===" . PHP_EOL;
$notifications = DB::table('notifications')->get();
foreach ($notifications as $notif) {
    echo "ID: {$notif->id}" . PHP_EOL;
    echo "Type: {$notif->type}" . PHP_EOL;
    echo "Data: {$notif->data}" . PHP_EOL;
    echo "---" . PHP_EOL;
}

// Test Laravel methods
echo PHP_EOL . "=== Laravel Methods Test ===" . PHP_EOL;
$unreadCount = $user->unreadNotifications()->count();
echo "Unread notifications: {$unreadCount}" . PHP_EOL;

if ($unreadCount > 0) {
    echo PHP_EOL . "=== Sample Notification Data ===" . PHP_EOL;
    $sampleNotif = $user->unreadNotifications()->first();
    $data = json_decode($sampleNotif->data, true);
    echo "Title: " . ($data['title'] ?? 'No title') . PHP_EOL;
    echo "Body: " . ($data['body'] ?? 'No body') . PHP_EOL;
    echo "Has actions: " . (isset($data['actions']) ? 'Yes' : 'No') . PHP_EOL;
}
