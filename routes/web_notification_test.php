<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;

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
