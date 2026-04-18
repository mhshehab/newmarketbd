<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Notification Data Format Test ===" . PHP_EOL;

// Check what format the data is actually stored in
$notifications = DB::table('notifications')->get();

foreach ($notifications as $notification) {
    echo "Notification ID: {$notification->id}" . PHP_EOL;
    echo "Data type: " . gettype($notification->data) . PHP_EOL;
    echo "Data content: " . var_export($notification->data, true) . PHP_EOL;
    
    // Try to decode if it's a string
    if (is_string($notification->data)) {
        $decoded = json_decode($notification->data, true);
        echo "Decoded successfully: " . ($decoded !== null ? 'Yes' : 'No') . PHP_EOL;
        if ($decoded !== null) {
            echo "Title: " . ($decoded['title'] ?? 'No title') . PHP_EOL;
        }
    } elseif (is_array($notification->data)) {
        echo "Data is already an array" . PHP_EOL;
        echo "Title: " . ($notification->data['title'] ?? 'No title') . PHP_EOL;
    }
    
    echo "---" . PHP_EOL;
}

// Let's also check how Laravel's DatabaseNotification model handles this
echo PHP_EOL . "=== Laravel DatabaseNotification Test ===" . PHP_EOL;

use Illuminate\Notifications\DatabaseNotification;

$dbNotifications = DatabaseNotification::all();
echo "Total DatabaseNotification records: " . $dbNotifications->count() . PHP_EOL;

foreach ($dbNotifications as $dbNotif) {
    echo "ID: {$dbNotif->id}" . PHP_EOL;
    echo "Data type: " . gettype($dbNotif->data) . PHP_EOL;
    
    if (is_array($dbNotif->data)) {
        echo "Title from array: " . ($dbNotif->data['title'] ?? 'No title') . PHP_EOL;
    } else {
        $decoded = json_decode($dbNotif->data, true);
        echo "Title from decoded: " . ($decoded['title'] ?? 'No title') . PHP_EOL;
    }
    echo "---" . PHP_EOL;
}
