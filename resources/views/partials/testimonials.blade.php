<div class="p-6 bg-gray-50">
    <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
        <span class="w-2 h-6 bg-blue-600 rounded-full"></span> What Our Customers Say
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($testimonials as $testimonial)
            <div class="bg-white rounded-lg p-6 shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100">
                <div class="flex items-center mb-4">
                    @if($testimonial->customer_image)
                        <img src="{{ asset('storage/' . $testimonial->customer_image) }}" 
                             alt="{{ $testimonial->customer_name }}" 
                             class="w-12 h-12 rounded-full object-cover mr-4">
                    @else
                        <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold mr-4">
                            {{ substr($testimonial->customer_name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h4 class="font-bold text-gray-800">{{ $testimonial->customer_name }}</h4>
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $testimonial->rating)
                                    <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-gray-300 fill-current" viewBox="0 0 20 20">
                                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                    </svg>
                                @endif
                            @endfor
                        </div>
                    </div>
                </div>
                <p class="text-gray-600 italic text-sm leading-relaxed">
                    "{{ $testimonial->testimonial_text }}"
                </p>
            </div>
        @empty
            <div class="col-span-full text-center py-8">
                <p class="text-gray-500">No testimonials available.</p>
            </div>
        @endforelse
    </div>
</div>
