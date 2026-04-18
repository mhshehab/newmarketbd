<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// পাবলিক রাউট (ক্রেতাদের জন্য)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');

// নোট: অ্যাডমিন প্যানেলের জন্য আলাদা রাউট এখানে প্রয়োজন নেই। 
// ফিলামেন্ট অটোমেটিক '/admin' রাউটটি হ্যান্ডেল করবে।

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

/*
|--------------------------------------------------------------------------
| Authentication Routes (Optional)
|--------------------------------------------------------------------------
| আপনি যদি লারাভেল ব্রিজ বা ফোর্ট্রিফাই ব্যবহার করেন তবে তাদের রাউটগুলো নিচে থাকতে পারে।
*/