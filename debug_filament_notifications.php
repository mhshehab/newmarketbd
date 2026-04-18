<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Deep Filament Notification Debug ===" . PHP_EOL;

use App\Models\User;

// 1. Check if user exists and can be authenticated
$user = User::find(1);
if (!$user) {
    echo "User ID 1 not found!" . PHP_EOL;
    exit;
}

echo "User found: {$user->name} ({$user->email})" . PHP_EOL;

// 2. Check if user has the correct auth guard
echo PHP_EOL . "=== Authentication Check ===" . PHP_EOL;
echo "User auth guard: " . (auth()->check() ? 'Authenticated' : 'Not authenticated') . PHP_EOL;
echo "Web guard user: " . (auth('web')->check() ? 'Authenticated' : 'Not authenticated') . PHP_EOL;

// 3. Check notifications with exact Filament query
echo PHP_EOL . "=== Filament Query Simulation ===" . PHP_EOL;

// This is the exact query Filament uses
$notifications = DB::table('notifications')
    ->where('notifiable_type', 'App\\Models\\User')
    ->where('notifiable_id', $user->id)
    ->whereNull('read_at')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

echo "Direct query results: " . $notifications->count() . " notifications" . PHP_EOL;

foreach ($notifications as $notif) {
    echo "- ID: {$notif->id}" . PHP_EOL;
    echo "  Type: {$notif->type}" . PHP_EOL;
    echo "  Data: {$notif->data}" . PHP_EOL;
    echo "---" . PHP_EOL;
}

// 4. Test with Laravel's DatabaseNotification model
echo PHP_EOL . "=== DatabaseNotification Model Test ===" . PHP_EOL;

use Illuminate\Notifications\DatabaseNotification;

$dbNotifications = DatabaseNotification::where('notifiable_type', 'App\\Models\\User')
    ->where('notifiable_id', $user->id)
    ->whereNull('read_at')
    ->get();

echo "DatabaseNotification results: " . $dbNotifications->count() . " notifications" . PHP_EOL;

// 5. Test user relationship
echo PHP_EOL . "=== User Relationship Test ===" . PHP_EOL;

$userNotifications = $user->unreadNotifications()->get();
echo "User unreadNotifications: " . $userNotifications->count() . PHP_EOL;

foreach ($userNotifications as $notif) {
    echo "- ID: {$notif->id}" . PHP_EOL;
    echo "  Title: " . ($notif->data['title'] ?? 'No title') . PHP_EOL;
    echo "---" . PHP_EOL;
}

// 6. Check if there's a session issue
echo PHP_EOL . "=== Session Check ===" . PHP_EOL;
echo "Session ID: " . session()->getId() . PHP_EOL;
echo "Session has user: " . (session()->has('auth.password_confirmed_at') ? 'Yes' : 'No') . PHP_EOL;

// 7. Create a Filament-compatible notification
echo PHP_EOL . "=== Create Filament Compatible Notification ===" . PHP_EOL;

// Delete all existing notifications
DB::table('notifications')->delete();

// Create notification in exact Filament format
$filamentNotification = [
    'id' => (string)\Illuminate\Support\Str::uuid(),
    'type' => 'App\\Notifications\\DatabaseNotification',
    'notifiable_type' => 'App\\Models\\User',
    'notifiable_id' => $user->id,
    'data' => json_encode([
        'title' => 'Filament Test',
        'body' => 'This is a Filament-compatible notification',
        'icon' => 'heroicon-o-bell',
        'type' => 'info'
    ]),
    'read_at' => null,
    'created_at' => now(),
    'updated_at' => now(),
];

DB::table('notifications')->insert($filamentNotification);
echo "Filament notification created with ID: {$filamentNotification['id']}" . PHP_EOL;

// Verify it exists
$verifyCount = DB::table('notifications')->where('id', $filamentNotification['id'])->count();
echo "Verification: {$verifyCount} notification found" . PHP_EOL;
