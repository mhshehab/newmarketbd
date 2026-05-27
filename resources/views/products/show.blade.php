@extends('layouts.app')

@section('title', $product->name . ' - ' . \App\Models\Setting::getValue('website_name', 'Default Name'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-6 text-sm">
        <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-700">Home</a>
        <span class="mx-2 text-gray-400">/</span>
        @if($product->category)
            <a href="{{ route('category.show', $product->category->slug) }}" class="text-gray-500 hover:text-gray-700">
                {{ $product->category->name }}
            </a>
            <span class="mx-2 text-gray-400">/</span>
        @endif
        <span class="text-gray-800 font-medium">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Product Image -->
        <div class="space-y-4">
            <div class="aspect-square bg-gray-50 rounded-lg overflow-hidden">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" 
                         alt="{{ $product->name }}" 
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @endif
            </div>
        </div>

        <!-- Product Details -->
        <div class="space-y-6">
            <!-- Product Name -->
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">{{ $product->name }}</h1>
                @if($product->category)
                    <p class="text-sm text-gray-500 mt-1">
                        Category: <a href="{{ route('category.show', $product->category->slug) }}" class="hover:text-pink-600">
                            {{ $product->category->name }}
                        </a>
                    </p>
                @endif
            </div>

            <!-- Price -->
            <div class="flex items-center gap-3">
                @if($product->discount_price > 0)
                    <span class="text-3xl font-bold text-pink-600">BDT {{ number_format($product->discount_price, 0) }}</span>
                    <span class="text-xl text-gray-400 line-through">BDT {{ number_format($product->price, 0) }}</span>
                    <span class="bg-red-500 text-white text-sm px-2 py-1 rounded-full font-bold">
                        {{ round((1 - $product->discount_price / $product->price) * 100) }}% OFF
                    </span>
                @else
                    <span class="text-3xl font-bold text-pink-600">BDT {{ number_format($product->price, 0) }}</span>
                @endif
            </div>

            <!-- Stock Status -->
            <div class="flex items-center gap-2">
                @if($product->stock_quantity > 0)
                    <span class="bg-green-100 text-green-800 text-sm px-3 py-1 rounded-full font-medium">
                        In Stock ({{ $product->stock_quantity }} units)
                    </span>
                @else
                    <span class="bg-red-100 text-red-800 text-sm px-3 py-1 rounded-full font-medium">
                        Out of Stock
                    </span>
                @endif
                
                @if($product->is_weighted_product)
                    <span class="bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full font-medium">
                        Weighted Product
                    </span>
                @endif
            </div>

            <!-- SKU -->
            <div class="text-sm text-gray-500">
                SKU: {{ $product->sku }}
            </div>

            <!-- Description -->
            @if($product->description)
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Description</h3>
                    <p class="text-gray-600 leading-relaxed">{{ $product->description }}</p>
                </div>
            @endif

            <!-- Additional Info -->
            @if($product->weight_per_unit)
                <div class="text-sm text-gray-600">
                    Weight per unit: {{ number_format($product->weight_per_unit, 3) }} kg
                </div>
            @endif

            @if($product->expiry_date)
                <div class="text-sm text-gray-600">
                    Expiry Date: {{ $product->expiry_date->format('d M, Y') }}
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4">
                <button class="flex-1 bg-pink-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-pink-700 transition-colors">
                    Add to Cart
                </button>
                <button class="bg-gray-100 text-gray-700 py-3 px-6 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    Wishlist
                </button>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="mt-12">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <span class="w-2 h-6 bg-pink-600 rounded-full"></span>
                Related Products
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($relatedProducts as $relatedProduct)
                    <div class="group bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300">
                        <div class="relative">
                            <a href="{{ route('products.show', $relatedProduct->slug) }}" class="block">
                                <div class="aspect-square overflow-hidden bg-gray-50">
                                    <img src="{{ $relatedProduct->image ? asset('storage/' . $relatedProduct->image) : asset('images/placeholder.jpg') }}" 
                                         alt="{{ $relatedProduct->name }}" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>
                            </a>
                            @if($relatedProduct->discount_price > 0)
                                <span class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full font-bold">
                                    -{{ round((1 - $relatedProduct->discount_price / $relatedProduct->price) * 100) }}%
                                </span>
                            @endif
                        </div>
                        <div class="p-3">
                            <a href="{{ route('products.show', $relatedProduct->slug) }}" class="block">
                                <h4 class="text-sm font-medium text-gray-800 mb-2 line-clamp-2 group-hover:text-pink-600 transition-colors">
                                    {{ $relatedProduct->name }}
                                </h4>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        @if($relatedProduct->discount_price > 0)
                                            <span class="text-lg font-bold text-pink-600">BDT {{ number_format($relatedProduct->discount_price, 0) }}</span>
                                            <span class="text-xs text-gray-400 line-through">BDT {{ number_format($relatedProduct->price, 0) }}</span>
                                        @else
                                            <span class="text-lg font-bold text-pink-600">BDT {{ number_format($relatedProduct->price, 0) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
