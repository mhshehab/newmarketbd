<div class="p-6">
    <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
        <span class="w-2 h-6 bg-pink-600 rounded-full"></span> Shop By Category
    </h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-8 gap-4 p-4">
        @php $displayItems = isset($subCategories) ? $subCategories : $categories; @endphp
        @foreach($displayItems as $item)
            <a href="{{ route('category.show', $item->slug) }}" class="group bg-white border border-gray-200 rounded-lg p-4 flex flex-col items-center justify-center shadow-sm hover:shadow-md transition-all duration-300">
                <div class="w-24 h-24 mb-4 flex items-center justify-center">
                    <img src="{{ asset('storage/' . $item->image) }}" class="w-full h-full object-contain">
                </div>
                <h4 class="text-sm font-medium text-gray-700 text-center">{{ $item->name }}</h4>
            </a>
        @endforeach
    </div>
</div>