<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show($slug)
    {
        // ক্যাটাগরি এবং তার চাইল্ডগুলো লোড করা হচ্ছে
        $category = Category::where('slug', $slug)->with('children')->firstOrFail();

        // সাইডবার মেনু যেন সব পেজে কাজ করে, তাই মেইন ক্যাটাগরিগুলো নেওয়া হচ্ছে
        $categories = Category::where('parent_id', null)->with('children.children')->get();

        // ১. যদি এই ক্যাটাগরির আন্ডারে আরও সাব-ক্যাটাগরি থাকে
        if ($category->children->count() > 0) {
            return view('category_page', [
                'category' => $category,
                'subCategories' => $category->children,
                'categories' => $categories // সাইডবারের জন্য
            ]);
        }

        // ২. যদি কোনো সাব-ক্যাটাগরি না থাকে, তবে সরাসরি প্রোডাক্ট পেজ দেখাবে
        $products = $category->products()->paginate(20); // প্রোডাক্টগুলো লোড করা

        return view('product_page', [
            'category' => $category,
            'products' => $products,
            'categories' => $categories // সাইডবারের জন্য
        ]);
    }
}