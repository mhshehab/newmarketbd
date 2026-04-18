<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Filament Notification API ===" . PHP_EOL;

use App\Models\User;

// Get the user
$user = User::find(1);

if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

echo "User: {$user->name} (ID: {$user->id})" . PHP_EOL;

// Simulate the API call Filament makes for notifications
echo PHP_EOL . "=== Simulating Filament API Call ===" . PHP_EOL;

// Get unread notifications (what Filament polls)
$unreadNotifications = $user->unreadNotifications()
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

echo "Unread notifications for API: " . $unreadNotifications->count() . PHP_EOL;

// Format as Filament expects
$formattedNotifications = [];
foreach ($unreadNotifications as $notification) {
    $data = $notification->data;
    $formattedNotifications[] = [
        'id' => $notification->id,
        'title' => $data['title'] ?? 'Notification',
        'body' => $data['body'] ?? '',
        'type' => $data['type'] ?? 'info',
        'icon' => $data['icon'] ?? 'heroicon-o-bell',
        'actions' => $data['actions'] ?? [],
        'created_at' => $notification->created_at->toISOString(),
    ];
}

echo "Formatted notifications:" . PHP_EOL;
echo json_encode($formattedNotifications, JSON_PRETTY_PRINT) . PHP_EOL;

// Test the exact endpoint Filament would use
echo PHP_EOL . "=== Testing Filament Route ===" . PHP_EOL;
echo "Filament should be polling: /admin/notifications" . PHP_EOL;
echo "This should return JSON with the notifications above." . PHP_EOL;
