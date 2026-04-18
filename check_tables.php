<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Database Tables ===" . PHP_EOL;

$tables = DB::select('SHOW TABLES');

foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    echo "- " . $tableName . PHP_EOL;
    
    if ($tableName === 'notifications') {
        echo "  Found notifications table!" . PHP_EOL;
        $count = DB::table('notifications')->count();
        echo "  Total records: " . $count . PHP_EOL;
    }
}
