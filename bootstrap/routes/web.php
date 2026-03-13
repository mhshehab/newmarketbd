<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController; // এই লাইনটি অবশ্যই থাকতে হবে
use App\Http\Controllers\CategoryController;

//Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');

// এটি নিশ্চিত করুন
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// এবং আপনার ক্যাটাগরি শো রাউট
Route::get('/category/{slug}', [App\Http\Controllers\CategoryController::class, 'show'])->name('category.show');