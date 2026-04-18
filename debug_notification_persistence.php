<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Notification Persistence Issue ===" . PHP_EOL;

use App\Models\User;
use App\Models\Product;

// Clear notifications first
DB::table('notifications')->delete();
echo "Cleared all notifications." . PHP_EOL;

// Get user
$user = User::find(1);
if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

echo "User: {$user->name}" . PHP_EOL;

// Get expiring products
$today = now()->startOfDay();
$thresholdDate = now()->addDays(15)->endOfDay();

$expiringProducts = Product::whereNotNull('expiry_date')
    ->whereBetween('expiry_date', [$today, $thresholdDate])
    ->get();

echo "Found {$expiringProducts->count()} expiring products." . PHP_EOL;

if ($expiringProducts->count() > 0) {
    $product = $expiringProducts->first();
    echo "Creating notification for: {$product->name}" . PHP_EOL;
    
    // Create notification with action
    $editUrl = "/admin/products/{$product->id}/edit";
    
    \Filament\Notifications\Notification::make()
        ->title('Product Expiry Warning')
        ->body("Product: {$product->name}")
        ->warning()
        ->icon('heroicon-o-calendar')
        ->actions([
            \Filament\Notifications\Actions\Action::make('view')
                ->label('Fix Expiry Date')
                ->url($editUrl)
                ->color('primary'),
        ])
        ->sendToDatabase($user);
    
    echo "Notification created with action!" . PHP_EOL;
    
    // Check database
    $count = DB::table('notifications')->count();
    echo "Notifications in database: {$count}" . PHP_EOL;
    
    if ($count > 0) {
        $notification = DB::table('notifications')->first();
        echo "Notification details:" . PHP_EOL;
        echo "ID: {$notification->id}" . PHP_EOL;
        echo "Type: {$notification->type}" . PHP_EOL;
        echo "Data: {$notification->data}" . PHP_EOL;
        echo "Read At: " . ($notification->read_at ?? 'Unread') . PHP_EOL;
        
        // Test Laravel methods
        $unreadCount = $user->unreadNotifications()->count();
        echo "Unread notifications: {$unreadCount}" . PHP_EOL;
        
        // Check if notification has proper structure for Filament
        $data = json_decode($notification->data, true);
        if ($data) {
            echo "Title: " . ($data['title'] ?? 'No title') . PHP_EOL;
            echo "Body: " . ($data['body'] ?? 'No body') . PHP_EOL;
            echo "Has actions: " . (isset($data['actions']) ? 'Yes' : 'No') . PHP_EOL;
            
            if (isset($data['actions'])) {
                foreach ($data['actions'] as $action) {
                    echo "Action: " . ($action['label'] ?? 'No label') . PHP_EOL;
                    echo "URL: " . ($action['url'] ?? 'No URL') . PHP_EOL;
                }
            }
        }
        
        echo PHP_EOL . "=== Testing Persistence ===" . PHP_EOL;
        echo "The notification should persist until manually deleted." . PHP_EOL;
        echo "Clicking the action should redirect to: {$editUrl}" . PHP_EOL;
        echo "After clicking, the notification should remain in the list." . PHP_EOL;
    }
} else {
    echo "No expiring products found." . PHP_EOL;
}

echo PHP_EOL . "=== Manual Testing Instructions ===" . PHP_EOL;
echo "1. Go to: http://127.0.0.1:8001/admin/login" . PHP_EOL;
echo "2. Login and check notification bell" . PHP_EOL;
echo "3. Click on the notification action button" . PHP_EOL;
echo "4. Verify it redirects to product edit page" . PHP_EOL;
echo "5. Check if notification still appears in the list" . PHP_EOL;
