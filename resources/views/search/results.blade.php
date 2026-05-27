@extends('layouts.app')

@section('title', 'Search Results - ' . \App\Models\Setting::getValue('website_name', 'Default Name'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Search Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">
            Search Results for "{{ $query }}"
        </h1>
        <p class="text-gray-600">
            Found {{ $products->total() }} products
        </p>
    </div>

    <!-- Category Suggestions -->
    @if($categories->count() > 0)
        <div class="mb-8 bg-blue-50 rounded-lg p-6">
            <h2 class="text-lg font-bold text-blue-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Related Categories
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                @foreach($categories as $category)
                    <a href="{{ route('category.show', $category->slug) }}" 
                       class="bg-white border border-blue-200 rounded-lg p-3 text-center hover:bg-blue-100 transition-colors">
                        <h3 class="text-sm font-medium text-blue-800">{{ $category->name }}</h3>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Products Grid -->
    @if($products->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            @foreach($products as $product)
                <div class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100">
                    <!-- Product Image Section -->
                    <div class="relative">
                        <a href="{{ route('products.show', $product->slug) }}" class="block">
                            <div class="aspect-square bg-gray-50 overflow-hidden">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" 
                                         alt="{{ $product->name }}" 
                                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </a>
                        
                        <!-- Discount Badge -->
                        @if($product->discount_price > 0)
                            <span class="absolute top-3 right-3 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs px-3 py-1.5 rounded-full font-bold shadow-lg">
                                {{ round((1 - $product->discount_price / $product->price) * 100) }}% OFF
                            </span>
                        @endif
                        
                        <!-- Stock Status Badge -->
                        <div class="absolute top-3 left-3">
                            @if($product->stock_quantity > 0)
                                <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full font-medium shadow-lg">
                                    In Stock
                                </span>
                            @else
                                <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full font-medium shadow-lg">
                                    Out of Stock
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Product Details Section -->
                    <div class="p-4 space-y-3">
                        <!-- Product Name & Category -->
                        <div>
                            <a href="{{ route('products.show', $product->slug) }}" class="block">
                                <h3 class="font-semibold text-gray-900 text-lg leading-tight hover:text-pink-600 transition-colors line-clamp-2">
                                    {{ $product->name }}
                                </h3>
                                @if($product->category)
                                    <p class="text-sm text-gray-500 mt-1">
                                        <a href="{{ route('category.show', $product->category->slug) }}" class="hover:text-pink-600 transition-colors">
                                            {{ $product->category->name }}
                                        </a>
                                    </p>
                                @endif
                            </a>
                        </div>

                        <!-- Price Section -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @if($product->discount_price > 0)
                                    <span class="text-2xl font-bold text-pink-600">BDT {{ number_format($product->discount_price, 0) }}</span>
                                    <span class="text-sm text-gray-400 line-through">BDT {{ number_format($product->price, 0) }}</span>
                                @else
                                    <span class="text-2xl font-bold text-pink-600">BDT {{ number_format($product->price, 0) }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>SKU: {{ $product->sku }}</span>
                            @if($product->is_weighted_product)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full font-medium">
                                    Weighted
                                </span>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 pt-2">
                            <button class="flex-1 bg-pink-600 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-pink-700 transition-colors text-sm">
                                Add to Cart
                            </button>
                            <button class="bg-gray-100 text-gray-700 py-2.5 px-3 rounded-lg hover:bg-gray-200 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            {{ $products->links() }}
        </div>
    @else
        <div class="text-center py-16">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h2 class="text-xl font-bold text-gray-800 mb-2">No products found</h2>
            <p class="text-gray-600 mb-6">
                No products found for "{{ $query }}". Try searching with different keywords.
            </p>
            <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Back to Home
            </a>
        </div>
    @endif
</div>
@endsection
