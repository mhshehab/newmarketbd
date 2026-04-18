<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$db = $app->make('db');
$has = $db->connection()->getSchemaBuilder()->hasColumn('products', 'expiry_date');
echo $has ? 'COLUMN_EXISTS' : 'COLUMN_MISSING';
echo PHP_EOL;
$product = App\Models\Product::first();
echo 'FIRST_EXPIRY=' . ($product ? $product->expiry_date : 'NONE') . PHP_EOL;
