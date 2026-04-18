<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Filament Notification Endpoint ===" . PHP_EOL;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Simulate authentication
$user = User::find(1);
Auth::login($user);

echo "Authenticated user: " . Auth::user()->name . PHP_EOL;

// Test the notification endpoint logic
echo PHP_EOL . "=== Simulating Filament Endpoint Response ===" . PHP_EOL;

$notifications = $user->unreadNotifications()
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

$response = [
    'notifications' => $notifications->map(function ($notification) {
        $data = $notification->data;
        return [
            'id' => $notification->id,
            'title' => $data['title'] ?? 'Notification',
            'body' => $data['body'] ?? '',
            'type' => $data['type'] ?? 'info',
            'icon' => $data['icon'] ?? 'heroicon-o-bell',
            'actions' => $data['actions'] ?? [],
            'created_at' => $notification->created_at->toISOString(),
        ];
    })->toArray(),
    'count' => $notifications->count(),
];

echo "Response that should be sent to frontend:" . PHP_EOL;
echo json_encode($response, JSON_PRETTY_PRINT) . PHP_EOL;

echo PHP_EOL . "=== Browser Testing Instructions ===" . PHP_EOL;
echo "1. Open browser and go to: http://127.0.0.1:8000/admin" . PHP_EOL;
echo "2. Login to the admin panel" . PHP_EOL;
echo "3. Open browser developer tools (F12)" . PHP_EOL;
echo "4. Go to Network tab" . PHP_EOL;
echo "5. Look for requests to '/admin/notifications'" . PHP_EOL;
echo "6. Check if the request is successful and returns JSON" . PHP_EOL;
