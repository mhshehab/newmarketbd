<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
{
    return [
        // ১. মোট বিক্রয়
        Stat::make('Total Sales', '৳' . (Order::where('status', 'delivered')->sum('total_amount') ?? 0))
            ->description('মোট আয়')
            ->descriptionIcon('heroicon-m-banknotes')
            ->color('success'),

        // ২. মোট অর্ডার
        Stat::make('Total Orders', Order::count())
            ->description('সর্বমোট অর্ডার')
            ->descriptionIcon('heroicon-m-shopping-cart'),

        // ৩. মোট প্রোডাক্ট (এটি আপনি যোগ করতে চেয়েছিলেন)
        Stat::make('Total Products', Product::count())
            ->description('স্টকে থাকা মোট আইটেম')
            ->descriptionIcon('heroicon-m-squares-2x2')
            ->color('info'),

        // ৪. লো স্টক অ্যালার্ট
        Stat::make('Low Stock Alert', Product::where('stock', '<', 5)->count())
            ->description('পুনরায় স্টক করা প্রয়োজন')
            ->descriptionIcon('heroicon-m-exclamation-triangle')
            ->color('danger'),
    ];
}
}