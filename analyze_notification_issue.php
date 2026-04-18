<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Notification Issue Analysis ===" . PHP_EOL;

use App\Models\User;
use App\Models\Product;

// Test 1: Check if notifications are being created
echo "Test 1: Creating notification..." . PHP_EOL;

// Clear first
DB::table('notifications')->delete();

// Get expiring product
$product = Product::whereNotNull('expiry_date')
    ->where('expiry_date', '<=', now()->addDays(15))
    ->first();

if ($product) {
    echo "Found product: {$product->name}" . PHP_EOL;
    
    // Create notification
    $notification = \Filament\Notifications\Notification::make()
        ->title('Test Notification')
        ->body('This is a test notification')
        ->warning()
        ->icon('heroicon-o-bell')
        ->actions([
            \Filament\Notifications\Actions\Action::make('view')
                ->label('View Product')
                ->url("/admin/products/{$product->id}/edit")
                ->color('primary')
        ])
        ->sendToDatabase(User::find(1));
    
    echo "Notification sent via Filament!" . PHP_EOL;
    
    // Check database
    $count = DB::table('notifications')->count();
    echo "Notifications in database: {$count}" . PHP_EOL;
    
    if ($count > 0) {
        echo PHP_EOL . "Test 2: Checking notification structure..." . PHP_EOL;
        
        $notif = DB::table('notifications')->first();
        echo "Type: {$notif->type}" . PHP_EOL;
        echo "Data: {$notif->data}" . PHP_EOL;
        
        // Test Laravel methods
        $user = User::find(1);
        $unreadCount = $user->unreadNotifications()->count();
        echo "Unread via Laravel: {$unreadCount}" . PHP_EOL;
        
        echo PHP_EOL . "Test 3: Filament compatibility check..." . PHP_EOL;
        
        // Check if this matches Filament's expected format
        $data = json_decode($notif->data, true);
        if ($data && isset($data['title'])) {
            echo "✓ Has title: " . $data['title'] . PHP_EOL;
        } else {
            echo "✗ Missing title" . PHP_EOL;
        }
        
        if ($data && isset($data['body'])) {
            echo "✓ Has body: " . $data['body'] . PHP_EOL;
        } else {
            echo "✗ Missing body" . PHP_EOL;
        }
        
        if ($data && isset($data['actions'])) {
            echo "✓ Has actions" . PHP_EOL;
        } else {
            echo "✗ Missing actions" . PHP_EOL;
        }
        
        echo PHP_EOL . "=== POSSIBLE ISSUES ===" . PHP_EOL;
        echo "1. Filament might expect different data structure" . PHP_EOL;
        echo "2. Notification type might be wrong" . PHP_EOL;
        echo "3. Panel configuration issue" . PHP_EOL;
        echo "4. Browser cache issue" . PHP_EOL;
        echo "5. JavaScript error in panel" . PHP_EOL;
        
        echo PHP_EOL . "=== SOLUTIONS ===" . PHP_EOL;
        echo "1. Try manual notification creation:" . PHP_EOL;
        echo "   php artisan tinker" . PHP_EOL;
        echo "   >>> \$user = App\\Models\\User::find(1)" . PHP_EOL;
        echo "   >>> \$user->notify(new \\Illuminate\\Notifications\\DatabaseNotification(['title' => 'Test', 'data' => ['body' => 'Test']]))" . PHP_EOL;
        echo PHP_EOL;
        echo "2. Check Filament docs for notification format" . PHP_EOL;
        echo "3. Clear browser cache and Laravel cache" . PHP_EOL;
        echo "4. Check browser console for errors" . PHP_EOL;
        echo "5. Try different notification type" . PHP_EOL;
        
    } else {
        echo "ERROR: Notification not saved to database!" . PHP_EOL;
    }
} else {
    echo "No expiring products found." . PHP_EOL;
}

echo PHP_EOL . "=== ANALYSIS COMPLETE ===" . PHP_EOL;
