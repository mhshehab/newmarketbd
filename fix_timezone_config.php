<?php

echo "=== Fix Timezone Configuration ===" . PHP_EOL;

// Update config/app.php directly
$configFile = 'config/app.php';

if (file_exists($configFile)) {
    $content = file_get_contents($configFile);
    
    // Find and replace timezone setting
    if (strpos($content, "'timezone' =>") !== false) {
        $content = preg_replace("/'timezone' => '.*?'/", "'timezone' => 'Asia/Dhaka'", $content);
        echo "Updated timezone in config/app.php" . PHP_EOL;
    } else {
        echo "Could not find timezone setting in config/app.php" . PHP_EOL;
    }
    
    file_put_contents($configFile, $content);
    echo "Config file updated!" . PHP_EOL;
} else {
    echo "config/app.php file not found!" . PHP_EOL;
}

echo PHP_EOL . "Clearing cache..." . PHP_EOL;
shell_exec('php artisan config:clear');
shell_exec('php artisan cache:clear');
echo "Cache cleared!" . PHP_EOL;
