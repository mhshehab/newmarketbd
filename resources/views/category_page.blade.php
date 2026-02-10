<!DOCTYPE html>
<html>
<head>
    <title>New Market BD</title>
    
</head>
<body>
    @extends('layouts.app')

@section('content')
    <div class="p-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6 bg-white p-3 rounded-lg shadow-sm">
            <a href="/" class="hover:text-[#fd1e4b]">Home</a>
            <span>/</span>
            <span class="font-bold text-gray-800">{{ $category->name }}</span>
        </nav>

        <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
            <span class="w-1.5 h-6 bg-[#fd1e4b] rounded-full"></span> 
            Shop By {{ $category->name }}
        </h3>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-6">
            @foreach($subCategories as $sub)
                <a href="{{ route('category.show', $sub->slug) }}" 
                class="group bg-white border border-gray-200 rounded-xl p-6 flex flex-col items-center justify-center shadow-sm hover:shadow-md transition-all duration-300 w-full max-w-[200px]">
                    
                    <div class="w-24 h-24 mb-4 flex items-center justify-center overflow-hidden">
                        @if($sub->image)
                            <img src="{{ asset('storage/' . $sub->image) }}" 
                                class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300">
                        @else
                            <img src="{{ asset('images/default.png') }}" class="w-16 h-16 opacity-20">
                        @endif
                    </div>

                    <h4 class="text-sm font-semibold text-gray-700 text-center group-hover:text-[#fd1e4b] transition-colors">
                        {{ $sub->name }}
                    </h4>
                </a>
            @endforeach
        </div>
    </div>
    </div>
@endsection
</body>
</html>