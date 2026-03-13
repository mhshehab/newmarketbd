@extends('layouts.app')

@section('title', $category->name . ' - FreshCart')

@section('content')
<div class="p-6">
    <nav class="text-sm text-gray-500 mb-4">
        <a href="/" class="hover:text-pink-600">Home</a> 
        <span class="mx-2">/</span> 
        <span class="text-gray-800 font-bold">{{ $category->name }}</span>
    </nav>

    <h1 class="text-2xl font-bold mb-6">{{ $category->name }}</h1>

    @if($products->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            @foreach($products as $product)
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-40 object-contain mb-4">
                    <h3 class="font-bold text-gray-800">{{ $product->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $product->unit }}</p>
                    <div class="flex justify-between items-center mt-4">
                        <span class="font-bold text-pink-600">৳ {{ $product->price }}</span>
                        <button class="bg-pink-600 text-white px-3 py-1 rounded-lg text-sm font-bold hover:bg-pink-700">Add</button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-20 bg-white rounded-xl shadow-sm">
            <p class="text-gray-500 italic">No products found in this category.</p>
        </div>
    @endif
</div>
@endsection