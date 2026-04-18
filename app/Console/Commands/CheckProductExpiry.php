<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\User;
use Filament\Notifications\Notification;

class CheckProductExpiry extends Command
{
    protected $signature = 'app:check-product-expiry';
    protected $description = 'Check products for expiry and notify admins';

    public function handle()
{
    $today = now()->startOfDay();
    $thresholdDate = now()->addDays(15)->endOfDay();

    $expiringProducts = Product::whereNotNull('expiry_date')
        ->whereBetween('expiry_date', [$today, $thresholdDate])
        ->get();

    if ($expiringProducts->isEmpty()) {
        $this->info('কোনো পণ্য পাওয়া যায়নি।');
        return;
    }

    $admins = User::all();

    foreach ($expiringProducts as $product) {
        foreach ($admins as $admin) {
            \Filament\Notifications\Notification::make()
                ->title('মেয়াদ শেষ হচ্ছে!')
                ->body("পণ্য: {$product->name}")
                ->warning()
                ->icon('heroicon-o-calendar')
                ->actions([
                    \Filament\Notifications\Actions\Action::make('edit')
                        ->label('মেয়াদ ঠিক করুন')
                        ->url(fn() => "/admin/products/{$product->id}/edit")
                        ->button(),
                ])
                ->sendToDatabase($admin); // এটি ডাটাবেসে সেভ করবে
        }
    }

    $this->info('সফলভায় নোটিফিকেশন পাঠানো হয়েছে।');
}
}