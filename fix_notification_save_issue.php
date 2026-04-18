<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix Notification Save Issue ===" . PHP_EOL;

use App\Models\User;
use App\Models\Product;

// Clear notifications
DB::table('notifications')->delete();
echo "Cleared notifications." . PHP_EOL;

// Get user
$user = User::find(1);
if (!$user) {
    echo "User not found!" . PHP_EOL;
    exit;
}

echo "User: {$user->name}" . PHP_EOL;

// Get expiring product
$product = Product::whereNotNull('expiry_date')
    ->where('expiry_date', '<=', now()->addDays(15))
    ->first();

if ($product) {
    echo "Product found: {$product->name}" . PHP_EOL;
    
    // SOLUTION 1: Use Laravel's built-in notification system
    echo PHP_EOL . "=== SOLUTION 1: Laravel Built-in Notification ===" . PHP_EOL;
    
    try {
        // Create a proper Laravel notification
        $notification = new \Illuminate\Notifications\DatabaseNotification([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\ProductExpiryNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'title' => 'মেয়াদ শেষ হচ্ছে!',
                'body' => "পণ্য: {$product->name}",
                'icon' => 'heroicon-o-calendar',
                'type' => 'warning',
                'actions' => [
                    [
                        'label' => 'মেয়াদ ঠিক করুন',
                        'url' => "/admin/products/{$product->id}/edit",
                        'color' => 'primary'
                    ]
                ]
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Save using Laravel's relationship
        $user->notifications()->save($notification);
        echo "✓ Notification saved using Laravel relationship!" . PHP_EOL;
        
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . PHP_EOL;
    }
    
    // Check if it was saved
    $count = DB::table('notifications')->count();
    echo "Notifications in database: {$count}" . PHP_EOL;
    
    if ($count > 0) {
        echo PHP_EOL . "=== SOLUTION 2: Update CheckProductExpiry Command ===" . PHP_EOL;
        echo "Replace your CheckProductExpiry handle() method with:" . PHP_EOL;
        echo PHP_EOL;
        echo "public function handle()" . PHP_EOL;
        echo "{" . PHP_EOL;
        echo "    \$today = now()->startOfDay();" . PHP_EOL;
        echo "    \$thresholdDate = now()->addDays(15)->endOfDay();" . PHP_EOL;
        echo "    " . PHP_EOL;
        echo "    \$expiringProducts = Product::whereNotNull('expiry_date')" . PHP_EOL;
        echo "        ->whereBetween('expiry_date', [\$today, \$thresholdDate])" . PHP_EOL;
        echo "        ->get();" . PHP_EOL;
        echo "    " . PHP_EOL;
        echo "    if (\$expiringProducts->isEmpty()) {" . PHP_EOL;
        echo "        \$this->info('কোনো পণ্য পাওয়া যায়নি।');" . PHP_EOL;
        echo "        return;" . PHP_EOL;
        echo "    }" . PHP_EOL;
        echo "    " . PHP_EOL;
        echo "    \$admins = User::all();" . PHP_EOL;
        echo "    " . PHP_EOL;
        echo "    foreach (\$expiringProducts as \$product) {" . PHP_EOL;
        echo "        foreach (\$admins as \$admin) {" . PHP_EOL;
        echo "            \$notification = new \Illuminate\Notifications\DatabaseNotification([" . PHP_EOL;
        echo "                'id' => \Illuminate\Support\Str::uuid()," . PHP_EOL;
        echo "                'type' => 'App\\\\Notifications\\\\ProductExpiryNotification'," . PHP_EOL;
        echo "                'notifiable_type' => 'App\\\\Models\\\\User'," . PHP_EOL;
        echo "                'notifiable_id' => \$admin->id," . PHP_EOL;
        echo "                'data' => json_encode([" . PHP_EOL;
        echo "                    'title' => 'মেয়াদ শেষ হচ্ছে!'," . PHP_EOL;
        echo "                    'body' => \"পণ্য: {\$product->name}\"," . PHP_EOL;
        echo "                    'icon' => 'heroicon-o-calendar'," . PHP_EOL;
        echo "                    'type' => 'warning'," . PHP_EOL;
        echo "                    'actions' => [" . PHP_EOL;
        echo "                        [" . PHP_EOL;
        echo "                            'label' => 'মেয়াদ ঠিক করুন'," . PHP_EOL;
        echo "                            'url' => \"/admin/products/{\$product->id}/edit\"," . PHP_EOL;
        echo "                            'color' => 'primary'" . PHP_EOL;
        echo "                        ]" . PHP_EOL;
        echo "                    ]" . PHP_EOL;
        echo "                ])," . PHP_EOL;
        echo "                'read_at' => null," . PHP_EOL;
        echo "                'created_at' => now()," . PHP_EOL;
        echo "                'updated_at' => now()," . PHP_EOL;
        echo "            ]);" . PHP_EOL;
        echo "            " . PHP_EOL;
        echo "            \$admin->notifications()->save(\$notification);" . PHP_EOL;
        echo "        }" . PHP_EOL;
        echo "    }" . PHP_EOL;
        echo "    " . PHP_EOL;
        echo "    \$this->info('সফলভাবে নোটিফিকেশন পাঠানো হয়েছে।');" . PHP_EOL;
        echo "}" . PHP_EOL;
        
        echo PHP_EOL . "=== SOLUTION 3: Test Commands ===" . PHP_EOL;
        echo "1. Clear cache: php artisan cache:clear" . PHP_EOL;
        echo "2. Clear config: php artisan config:clear" . PHP_EOL;
        echo "3. Clear view: php artisan view:clear" . PHP_EOL;
        echo "4. Run command: php artisan app:check-product-expiry" . PHP_EOL;
        echo "5. Check database: php artisan tinker" . PHP_EOL;
        echo "   >>> DB::table('notifications')->count()" . PHP_EOL;
        
    } else {
        echo "ERROR: Notification still not saved!" . PHP_EOL;
    }
} else {
    echo "No expiring products found." . PHP_EOL;
}

echo PHP_EOL . "=== COMPLETE ===" . PHP_EOL;
