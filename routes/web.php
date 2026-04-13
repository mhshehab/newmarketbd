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

/*
|--------------------------------------------------------------------------
| Authentication Routes (Optional)
|--------------------------------------------------------------------------
| আপনি যদি লারাভেল ব্রিজ বা ফোর্ট্রিফাই ব্যবহার করেন তবে তাদের রাউটগুলো নিচে থাকতে পারে।
*/