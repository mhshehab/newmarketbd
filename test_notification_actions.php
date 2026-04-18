<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test Notification Actions and Persistence ===" . PHP_EOL;

use App\Models\User;
use App\Models\Product;

// Clear notifications
DB::table('notifications')->delete();
echo "Cleared all notifications." . PHP_EOL;

// Get user
$user = User::find(1);
if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

// Get expiring products
$today = now()->startOfDay();
$thresholdDate = now()->addDays(15)->endOfDay();

$expiringProducts = Product::whereNotNull('expiry_date')
    ->whereBetween('expiry_date', [$today, $thresholdDate])
    ->get();

if ($expiringProducts->count() > 0) {
    $product = $expiringProducts->first();
    echo "Creating notification for: {$product->name}" . PHP_EOL;
    
    // Create notification with proper Filament format
    $notification = \Filament\Notifications\Notification::make()
        ->title('মেয়াদ শেষ হচ্ছে!')
        ->body("পণ্য: {$product->name}")
        ->warning()
        ->icon('heroicon-o-calendar')
        ->actions([
            \Filament\Notifications\Actions\Action::make('edit')
                ->label('মেয়াদ ঠিক করুন')
                ->url("/admin/products/{$product->id}/edit")
                ->color('primary'),
        ])
        ->sendToDatabase($user);
    
    echo "Notification created with action!" . PHP_EOL;
    
    // Check database
    $count = DB::table('notifications')->count();
    echo "Notifications in database: {$count}" . PHP_EOL;
    
    if ($count > 0) {
        $notification = DB::table('notifications')->first();
        echo PHP_EOL . "=== Notification Details ===" . PHP_EOL;
        echo "ID: {$notification->id}" . PHP_EOL;
        echo "Type: {$notification->type}" . PHP_EOL;
        echo "Read At: " . ($notification->read_at ?? 'Unread') . PHP_EOL;
        
        $data = json_decode($notification->data, true);
        if ($data) {
            echo "Title: " . ($data['title'] ?? 'No title') . PHP_EOL;
            echo "Body: " . ($data['body'] ?? 'No body') . PHP_EOL;
            echo "Has actions: " . (isset($data['actions']) ? 'Yes' : 'No') . PHP_EOL;
            
            if (isset($data['actions'])) {
                foreach ($data['actions'] as $action) {
                    echo "Action: " . ($action['label'] ?? 'No label') . PHP_EOL;
                    echo "URL: " . ($action['url'] ?? 'No URL') . PHP_EOL;
                    echo "Color: " . ($action['color'] ?? 'No color') . PHP_EOL;
                }
            }
        }
        
        // Test Laravel methods
        echo PHP_EOL . "=== Laravel Methods Test ===" . PHP_EOL;
        $unreadCount = $user->unreadNotifications()->count();
        echo "Unread notifications: {$unreadCount}" . PHP_EOL;
        
        if ($unreadCount > 0) {
            $sample = $user->unreadNotifications()->first();
            $data = json_decode($sample->data, true);
            echo "Sample title: " . ($data['title'] ?? 'No title') . PHP_EOL;
            echo "Sample action: " . ($data['actions'][0]['label'] ?? 'No action') . PHP_EOL;
        }
        
        echo PHP_EOL . "=== Expected Behavior ===" . PHP_EOL;
        echo "1. Notification should appear in bell" . PHP_EOL;
        echo "2. Click notification should show details" . PHP_EOL;
        echo "3. Action button should redirect to: /admin/products/{$product->id}/edit" . PHP_EOL;
        echo "4. Notification should persist until manually deleted" . PHP_EOL;
        echo "5. Delete button should be available" . PHP_EOL;
    }
} else {
    echo "No expiring products found." . PHP_EOL;
}

echo PHP_EOL . "=== Testing Instructions ===" . PHP_EOL;
echo "1. Go to: http://127.0.0.1:8001/admin/login" . PHP_EOL;
echo "2. Login and check notification bell" . PHP_EOL;
echo "3. Click on notification to expand" . PHP_EOL;
echo "4. Click action button 'মেয়াদ ঠিক করুন'" . PHP_EOL;
echo "5. Verify redirect to product edit page" . PHP_EOL;
echo "6. Check if notification still appears" . PHP_EOL;
echo "7. Look for delete button in notification" . PHP_EOL;
