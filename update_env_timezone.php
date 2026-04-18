<?php

echo "=== Update .env Timezone ===" . PHP_EOL;

$envFile = '.env';
$timezoneLine = 'APP_TIMEZONE=Asia/Dhaka';

// Read current .env content
if (file_exists($envFile)) {
    $content = file_get_contents($envFile);
    echo "Current .env file found." . PHP_EOL;
    
    // Check if timezone already exists
    if (strpos($content, 'APP_TIMEZONE=') !== false) {
        echo "APP_TIMEZONE already exists in .env file." . PHP_EOL;
        
        // Replace existing timezone
        $content = preg_replace('/APP_TIMEZONE=.*/', $timezoneLine, $content);
        echo "Updated existing timezone setting." . PHP_EOL;
    } else {
        // Add new timezone line
        $content .= PHP_EOL . $timezoneLine . PHP_EOL;
        echo "Added new timezone setting." . PHP_EOL;
    }
    
    // Write back to file
    if (file_put_contents($envFile, $content)) {
        echo "Successfully updated .env file!" . PHP_EOL;
    } else {
        echo "Failed to update .env file." . PHP_EOL;
    }
} else {
    echo ".env file not found!" . PHP_EOL;
}

echo PHP_EOL . "=== Configuration Added ===" . PHP_EOL;
echo "APP_TIMEZONE=Asia/Dhaka" . PHP_EOL;
echo PHP_EOL . "Please run: php artisan config:clear" . PHP_EOL;
