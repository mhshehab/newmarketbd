<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// পাবলিক রাউট (ক্রেতাদের জন্য)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('products.show');

// Search routes
Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');

// নোট: অ্যাডমিন প্যানেলের জন্য আলাদা রাউট এখানে প্রয়োজন নেই। 
// ফিলামেন্ট অটোমেটিক '/admin' রাউটটি হ্যান্ডেল করবে।

// POS Invoice Download Route
Route::get('/pos/invoice/{id}/download', [App\Http\Controllers\POSInvoiceController::class, 'download'])->name('pos.invoice.download');

// Test notification route (for debugging)
Route::get('/test-notification', function () {
    // Only allow if user is logged in
    if (!auth()->check()) {
        return 'Please login first: <a href="/admin/login">Login</a>';
    }
    
    $user = auth()->user();
    
    // Clear existing notifications
    \Illuminate\Notifications\DatabaseNotification::where('notifiable_id', $user->id)->delete();
    
    // Create test notification
    $notification = new \Illuminate\Notifications\DatabaseNotification([
        'id' => \Illuminate\Support\Str::uuid(),
        'type' => 'App\Notifications\TestNotification',
        'notifiable_type' => 'App\Models\User',
        'notifiable_id' => $user->id,
        'data' => [
            'title' => 'Web Test Notification',
            'body' => 'This notification was created from web route',
            'icon' => 'heroicon-o-bell',
            'type' => 'success'
        ],
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $user->notifications()->save($notification);
    
    return 'Test notification created! <a href="/admin">Go to Admin Panel</a>';
});

// Cache Fix Route (Temporary - for fixing wrong cached data)
Route::get('/fix-cache', function () {
    // নির্দিষ্ট কী (Key) অনুযায়ী ক্যাশ মোছা
    Cache::forget('settings.website_logo');
    Cache::forget('settings.website_name');
    Cache::forget('settings.website_title');
    
    // অথবা সব সেটিংসের ক্যাশ একবারেই মুছে ফেলা
    Cache::flush();
    
    return "Cache Cleared! All settings cache has been removed. <a href='/admin'>Go to Admin Panel</a>";
});

/*
|--------------------------------------------------------------------------
| Authentication Routes (Optional)
|--------------------------------------------------------------------------
| আপনি যদি লারাভেল ব্রিজ বা ফোর্ট্রিফাই ব্যবহার করেন তবে তাদের রাউটগুলো নিচে থাকতে পারে।
*/