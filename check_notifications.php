<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Notifications Check ===" . PHP_EOL;

$notifications = DB::table('notifications')->latest()->limit(5)->get();

if ($notifications->isEmpty()) {
    echo "No notifications found in database." . PHP_EOL;
} else {
    echo "Found " . $notifications->count() . " notifications:" . PHP_EOL;
    
    foreach ($notifications as $notification) {
        echo "ID: " . $notification->id . PHP_EOL;
        echo "User ID: " . $notification->notifiable_id . PHP_EOL;
        echo "Type: " . $notification->type . PHP_EOL;
        echo "Data: " . $notification->data . PHP_EOL;
        echo "Created At: " . $notification->created_at . PHP_EOL;
        echo "---" . PHP_EOL;
    }
}

echo "=== Total Notifications Count ===" . PHP_EOL;
$totalCount = DB::table('notifications')->count();
echo "Total notifications in database: " . $totalCount . PHP_EOL;
