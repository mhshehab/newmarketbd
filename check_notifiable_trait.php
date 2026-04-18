<?php

echo "=== Check Notifiable Trait ===" . PHP_EOL;

// Check if the trait exists
$traitPath = 'vendor/laravel/framework/src/Illuminate/Notifications/Notifiable.php';
if (file_exists($traitPath)) {
    echo "Notifiable trait found at: {$traitPath}" . PHP_EOL;
} else {
    echo "Notifiable trait NOT found!" . PHP_EOL;
}

// Check alternative paths
$paths = [
    'vendor/laravel/framework/src/Illuminate/Notifications/Notifiable.php',
    'vendor/laravel/framework/src/Illuminate/Auth/Notifications/Notifiable.php',
    'vendor/laravel/framework/src/Illuminate/Notifications/NotifiableTrait.php'
];

foreach ($paths as $path) {
    if (file_exists($path)) {
        echo "Found at: {$path}" . PHP_EOL;
    }
}

// Check what's in the notifications directory
$notificationsDir = 'vendor/laravel/framework/src/Illuminate/Notifications/';
if (is_dir($notificationsDir)) {
    echo PHP_EOL . "Files in Notifications directory:" . PHP_EOL;
    $files = scandir($notificationsDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "- {$file}" . PHP_EOL;
        }
    }
}

// Check current User.php imports
$userFile = 'app/Models/User.php';
$content = file_get_contents($userFile);
echo PHP_EOL . "Current User.php imports:" . PHP_EOL;
if (strpos($content, 'use Illuminate\Notifiable;') !== false) {
    echo "✓ use Illuminate\\Notifications\\Notifiable;" . PHP_EOL;
} elseif (strpos($content, 'use Illuminate\Notifications\Notifiable;') !== false) {
    echo "✓ use Illuminate\\Notifications\\Notifiable;" . PHP_EOL;
} else {
    echo "✗ No Notifiable import found!" . PHP_EOL;
}
