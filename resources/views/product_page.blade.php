@extends('layouts.app')

@section('title', $category->name . ' - FreshCart')

@section('content')
<div class="p-6">
    <nav class="text-sm text-gray-500 mb-4">
        <a href="/" class="hover:text-pink-600 transition">Home</a> 
        <span class="mx-2 text-gray-300">/</span> 
        <span class="text-gray-800 font-bold">{{ $category->name }}</span>
    </nav>

    <h1 class="text-2xl font-bold mb-6 text-gray-900">{{ $category->name }}</h1>

    @if($products->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            @foreach($products as $product)
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition group">
                    <div class="aspect-square bg-gray-50 rounded-lg mb-4 flex items-center justify-center overflow-hidden">
                        <img src="{{ asset('storage/' . $product->image) }}" 
                             alt="{{ $product->name }}"
                             class="w-4/5 h-4/5 object-contain group-hover:scale-105 transition duration-300">
                    </div>
                    
                    <h3 class="font-bold text-gray-800 text-sm truncate mb-1">{{ $product->name }}</h3>
                    <p class="text-xs text-gray-500 mb-4">{{ $product->unit }}</p>
                    
                    <div class="flex justify-between items-center mt-auto">
                        <span class="font-bold text-pink-600">৳ {{ number_format($product->price, 2) }}</span>
                        <button class="bg-pink-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-pink-700 active:scale-95 transition-all">
                            Add
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-20 bg-white rounded-xl shadow-sm border border-dashed border-gray-200">
            <p class="text-gray-500 italic">No products found in this category.</p>
        </div>
    @endif
</div>
@endsection