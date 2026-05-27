<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return redirect()->route('home');
        }

        // Search products by name and description
        $products = Product::where('status', 1)
            ->where(function ($search) use ($query) {
                $search->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%")
                    ->orWhere('sku', 'LIKE', "%{$query}%");
            })
            ->with('category')
            ->paginate(12);

        // Search categories by name
        $categories = Category::where('name', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get();

        return view('search.results', compact('products', 'categories', 'query'));
    }

    public function autocomplete(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return response()->json([]);
        }

        // Get product suggestions
        $products = Product::where('status', 1)
            ->where('name', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'slug', 'image', 'price']);

        // Get category suggestions
        $categories = Category::where('name', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get(['id', 'name', 'slug']);

        $suggestions = [];

        // Add products to suggestions
        foreach ($products as $product) {
            $suggestions[] = [
                'type' => 'product',
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image' => $product->image ? asset('storage/' . $product->image) : null,
                'price' => $product->price,
                'url' => route('products.show', $product->slug)
            ];
        }

        // Add categories to suggestions
        foreach ($categories as $category) {
            $suggestions[] = [
                'type' => 'category',
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'image' => null,
                'price' => null,
                'url' => route('category.show', $category->slug)
            ];
        }

        return response()->json($suggestions);
    }
}
