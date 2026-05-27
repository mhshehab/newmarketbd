<div class="p-6 bg-white">
    <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
        <span class="w-2 h-6 bg-green-600 rounded-full"></span> Trusted Brands
    </h3>
    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-6">
        @forelse($brands as $brand)
            <div class="group flex items-center justify-center p-4 bg-gray-50 rounded-lg hover:bg-green-50 transition-all duration-300 border border-gray-200 hover:border-green-300">
                @if($brand->logo)
                    @if($brand->website_url)
                        <a href="{{ $brand->website_url }}" target="_blank" rel="noopener noreferrer" class="block">
                            <img src="{{ asset('storage/' . $brand->logo) }}" 
                                 alt="{{ $brand->name }}" 
                                 class="h-12 w-auto object-contain filter grayscale group-hover:grayscale-0 transition-all duration-300">
                        </a>
                    @else
                        <img src="{{ asset('storage/' . $brand->logo) }}" 
                             alt="{{ $brand->name }}" 
                             class="h-12 w-auto object-contain filter grayscale group-hover:grayscale-0 transition-all duration-300">
                    @endif
                @else
                    <div class="text-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-blue-500 rounded-lg flex items-center justify-center text-white font-bold text-sm mb-2">
                            {{ substr($brand->name, 0, 1) }}
                        </div>
                        <p class="text-xs text-gray-600 font-medium">{{ $brand->name }}</p>
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-full text-center py-8">
                <p class="text-gray-500">No brands available.</p>
            </div>
        @endforelse
    </div>
</div>
