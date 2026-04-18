<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Notification Table Structure Check ===" . PHP_EOL;

// Check if notifications table exists and its structure
$tables = DB::select('SHOW TABLES');
$notificationsTableExists = false;

foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    if ($tableName === 'notifications') {
        $notificationsTableExists = true;
        break;
    }
}

if (!$notificationsTableExists) {
    echo "Notifications table does not exist!" . PHP_EOL;
    exit;
}

echo "Notifications table exists." . PHP_EOL;

// Check table structure
$columns = DB::select('DESCRIBE notifications');
echo PHP_EOL . "Table structure:" . PHP_EOL;

foreach ($columns as $column) {
    echo "- {$column->Field}: {$column->Type} (Null: {$column->Null}, Default: {$column->Default})" . PHP_EOL;
}

// Check if there are any notifications
$count = DB::table('notifications')->count();
echo PHP_EOL . "Total notifications: {$count}" . PHP_EOL;

// Check a sample notification
$sample = DB::table('notifications')->first();
if ($sample) {
    echo PHP_EOL . "Sample notification:" . PHP_EOL;
    echo "- ID: {$sample->id}" . PHP_EOL;
    echo "- Type: {$sample->type}" . PHP_EOL;
    echo "- Notifiable Type: {$sample->notifiable_type}" . PHP_EOL;
    echo "- Notifiable ID: {$sample->notifiable_id}" . PHP_EOL;
    echo "- Data: {$sample->data}" . PHP_EOL;
    echo "- Read At: " . ($sample->read_at ?? 'NULL') . PHP_EOL;
    echo "- Created At: {$sample->created_at}" . PHP_EOL;
}

// Check if the user exists
$user = DB::table('users')->find(1);
echo PHP_EOL . "User check:" . PHP_EOL;
echo "- User ID 1 exists: " . ($user ? 'Yes' : 'No') . PHP_EOL;
if ($user) {
    echo "- User name: {$user->name}" . PHP_EOL;
    echo "- User email: {$user->email}" . PHP_EOL;
}
