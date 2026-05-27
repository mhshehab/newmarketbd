<div class="p-6">
    <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
        <span class="w-2 h-6 bg-pink-600 rounded-full"></span> Featured Products
    </h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @forelse($featuredProducts as $product)
            <div class="group bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="relative">
                    <a href="{{ route('products.show', $product->slug) }}" class="block">
                        <div class="aspect-square overflow-hidden bg-gray-50">
                            <img src="{{ asset('storage/' . $product->image) }}" 
                                 alt="{{ $product->name }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                    </a>
                    @if($product->discount_price > 0)
                        <span class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full font-bold">
                            -{{ round((1 - $product->discount_price / $product->price) * 100) }}%
                        </span>
                    @endif
                </div>
                <div class="p-3">
                    <a href="{{ route('products.show', $product->slug) }}" class="block">
                        <h4 class="text-sm font-medium text-gray-800 mb-2 line-clamp-2 group-hover:text-pink-600 transition-colors">
                            {{ $product->name }}
                        </h4>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @if($product->discount_price > 0)
                                    <span class="text-lg font-bold text-pink-600">BDT {{ number_format($product->discount_price, 0) }}</span>
                                    <span class="text-xs text-gray-400 line-through">BDT {{ number_format($product->price, 0) }}</span>
                                @else
                                    <span class="text-lg font-bold text-pink-600">BDT {{ number_format($product->price, 0) }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-8">
                <p class="text-gray-500">No featured products available.</p>
            </div>
        @endforelse
    </div>
</div>
