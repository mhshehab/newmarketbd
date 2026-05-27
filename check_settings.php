<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Check settings
echo "Current settings:\n";
$settings = App\Models\Setting::whereIn('key', ['website_logo', 'website_favicon'])->get();
foreach($settings as $s) {
    echo $s->key . ': ' . $s->value . ' (type: ' . $s->type . ')' . PHP_EOL;
}
