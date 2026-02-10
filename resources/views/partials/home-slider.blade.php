<div class="p-4">
    <div class="swiper mainSlider rounded-xl overflow-hidden h-64 md:h-80 lg:h-96">
        <div class="swiper-wrapper">
            @forelse($sliders as $slider)
                <div class="swiper-slide relative bg-pink-50 flex flex-col md:flex-row items-center px-6 md:px-16 h-full overflow-hidden">
                    <div class="flex-1 z-10 text-center md:text-left py-8">
                        <h2 class="text-2xl md:text-4xl font-extrabold text-gray-800 mb-2 uppercase italic leading-tight">{{ $slider->title }}</h2>
                        <p class="text-gray-600 mb-6 text-sm md:text-base">{{ $slider->subtitle }}</p>
                        <a href="{{ $slider->link }}" class="bg-gray-900 text-white px-6 py-2.5 md:px-10 md:py-3 rounded-full font-bold hover:bg-black transition inline-block">{{ $slider->button_text }}</a>
                    </div>
                    <div class="flex-1 flex justify-center md:justify-end items-center h-full">
                        <img src="{{ asset('storage/' . $slider->image) }}" class="h-40 md:h-64 lg:h-72 w-auto object-contain drop-shadow-2xl">
                    </div>
                </div>
            @empty
                <div class="swiper-slide bg-pink-50 flex items-center justify-center h-full"><p>No sliders available.</p></div>
            @endforelse
        </div>
        <div class="swiper-pagination"></div>
    </div>
</div>