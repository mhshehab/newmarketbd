<?php

namespace App\Http\Controllers; // এই লাইনটি ঠিক আছে কি না দেখুন

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Slider;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')->with('children')->get(); // সাব-ক্যাটাগরিসহ
        $sliders = Slider::all(); 
        $products = Product::latest()->take(12)->get();
        $categories = Category::whereNull('parent_id')
            ->with('children.children') // এটি ৩য় লেভেল পর্যন্ত ডেটা নিয়ে আসবে
            ->get();

        return view('welcome', compact('categories', 'sliders', 'products'));
    }
    

}
