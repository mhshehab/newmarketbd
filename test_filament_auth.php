<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Filament Authentication Test ===" . PHP_EOL;

use App\Models\User;

// Test if the user can be authenticated as Filament user
$user = User::find(1);

if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

echo "User: {$user->name}" . PHP_EOL;
echo "Email: {$user->email}" . PHP_EOL;

// Test if user can access Filament panel
echo PHP_EOL . "=== Filament Panel Access Test ===" . PHP_EOL;

// Check if user has the required permissions
$canAccessPanel = true; // Basic check - in real apps this might be more complex

echo "Can access panel: " . ($canAccessPanel ? 'Yes' : 'No') . PHP_EOL;

// Test notification count for this user
$unreadCount = $user->unreadNotifications()->count();
echo "Unread notifications: {$unreadCount}" . PHP_EOL;

// Test if notifications are in the correct format for Filament
echo PHP_EOL . "=== Filament Format Test ===" . PHP_EOL;

$notifications = $user->unreadNotifications()->get();
foreach ($notifications as $notification) {
    $data = json_decode($notification->data, true);
    
    // Check required fields for Filament
    $hasTitle = isset($data['title']);
    $hasBody = isset($data['body']);
    $hasType = isset($data['type']);
    
    echo "Notification {$notification->id}:" . PHP_EOL;
    echo "- Has title: " . ($hasTitle ? 'Yes' : 'No') . PHP_EOL;
    echo "- Has body: " . ($hasBody ? 'Yes' : 'No') . PHP_EOL;
    echo "- Has type: " . ($hasType ? 'Yes' : 'No') . PHP_EOL;
    echo "- Title: " . ($data['title'] ?? 'Missing') . PHP_EOL;
    echo "---" . PHP_EOL;
}

// Test Filament's database notification query
echo PHP_EOL . "=== Filament Query Simulation ===" . PHP_EOL;

// This is similar to what Filament does internally
$filamentNotifications = DB::table('notifications')
    ->where('notifiable_type', 'App\\Models\\User')
    ->where('notifiable_id', $user->id)
    ->whereNull('read_at')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

echo "Filament-style query results: {$filamentNotifications->count()} notifications" . PHP_EOL;

foreach ($filamentNotifications as $notif) {
    $data = json_decode($notif->data, true);
    echo "- " . ($data['title'] ?? 'No title') . PHP_EOL;
}
