<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Clear Notifications Table ===" . PHP_EOL;

// Clear all notifications
DB::table('notifications')->delete();
echo "All notifications cleared from database." . PHP_EOL;

// Verify table is empty
$count = DB::table('notifications')->count();
echo "Current notification count: {$count}" . PHP_EOL;

echo "Notifications table cleared successfully!" . PHP_EOL;
