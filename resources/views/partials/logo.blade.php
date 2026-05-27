<a href="/" class="flex items-center gap-2 group">
    @if(setting('website_logo'))
        <img src="{{ get_website_logo() }}" alt="{{ get_website_name() }}" class="h-8 w-auto object-contain">
    @else
        <div class="w-8 h-8 bg-pink-600 rounded flex items-center justify-center text-white font-bold group-hover:bg-pink-700">
            {{ substr(get_website_name(), 0, 2) }}
        </div>
    @endif
    <span class="text-xl font-bold text-gray-700 hidden lg:block">{{ get_website_name() }}</span>
</a>