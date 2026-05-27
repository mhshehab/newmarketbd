<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Slider;
use App\Models\Product;
use App\Models\Testimonial;
use App\Models\Brand;
use App\Models\Discount;

class HomeController extends Controller
{
    public function index()
    {
        // Existing data
        $categories = Category::whereNull('parent_id')
            ->with('children.children')
            ->get();
        $sliders = Slider::all();
        
        // Featured Products (latest 12 products)
        $featuredProducts = Product::latest()
            ->where('status', 'active')
            ->take(12)
            ->get();
        
        // Special Offers (products with discounts)
        $specialOffers = Product::where('discount_price', '>', 0)
            ->where('status', 'active')
            ->take(8)
            ->get();
        
        // Testimonials (active testimonials ordered by position)
        $testimonials = Testimonial::where('is_active', true)
            ->orderBy('position', 'asc')
            ->take(6)
            ->get();
        
        // Brands (active brands ordered by position)
        $brands = Brand::where('is_active', true)
            ->orderBy('position', 'asc')
            ->take(12)
            ->get();

        return view('welcome', compact(
            'categories', 
            'sliders', 
            'featuredProducts', 
            'specialOffers', 
            'testimonials', 
            'brands'
        ));
    }
}
