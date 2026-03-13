<aside x-show="sidebarOpen" x-cloak x-transition:enter="sidebar-transition transform transition" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="sidebar-transition transform transition" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="w-72 min-w-[280px] border-r h-[calc(100vh-64px)] sticky top-16 bg-white overflow-y-auto sidebar-scroll z-40">
        <nav class="py-2">
        @foreach($categories->where('parent_id', null) as $category)
            @php
                // বর্তমান URL-এর স্লাগ নেওয়া হচ্ছে
                $currentSlug = request()->segment(2);
                
                // চেক করা হচ্ছে এই ক্যাটাগরি বা এর আন্ডারে থাকা কোনো চাইল্ড বর্তমানে একটিভ কি না
                $isActive = request()->is('category/' . $category->slug . '*');
                
                // ক্যাটাগরি কি ওপেন থাকবে? (যদি নিজে একটিভ হয় বা কোনো চাইল্ড একটিভ থাকে)
                $shouldOpen = $isActive || ($category->descendants && $category->descendants->contains('slug', $currentSlug));
            @endphp

            <div x-data="{ open: {{ $shouldOpen ? 'true' : 'false' }} }" class="border-b border-gray-50">
                <div class="menu-row flex items-center justify-between group transition-all {{ $isActive && !request()->routeIs('category.show', ['slug' => $category->slug]) ? '' : ($isActive ? 'bg-pink-50 text-pink-600' : '') }}">
                    <a href="{{ route('category.show', $category->slug) }}" 
                    class="flex items-center gap-3 px-4 py-3 flex-1 menu-text font-semibold {{ $isActive ? 'text-pink-600' : 'text-gray-700' }} group-hover:text-pink-600">
                        <svg class="w-5 h-5 {{ $isActive ? 'text-pink-600' : 'text-gray-400' }} group-hover:text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        {{ $category->name }}
                    </a>
                    
                    @if($category->children->count() > 0)
                        <button @click.prevent="open = !open" class="pr-4 py-3 text-gray-400 hover:text-pink-600">
                            <svg :class="open ? 'rotate-90' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    @endif
                </div>

                <div x-show="open" x-cloak x-collapse class="bg-gray-50">
                    @foreach($category->children as $sub)
                        @php
                            $isSubActive = request()->is('category/' . $sub->slug . '*');
                            $subShouldOpen = $isSubActive || ($sub->children && $sub->children->contains('slug', $currentSlug));
                        @endphp
                        
                        <div x-data="{ subOpen: {{ $subShouldOpen ? 'true' : 'false' }} }">
                            <div class="menu-row flex items-center justify-between group transition-all {{ $isSubActive ? 'bg-pink-100/50' : '' }}">
                                <a href="{{ route('category.show', $sub->slug) }}" 
                                class="flex items-center gap-3 pl-10 py-2.5 flex-1 menu-text font-medium {{ $isSubActive ? 'text-pink-600' : 'text-gray-600' }} group-hover:text-pink-600">
                                    <span class="w-1.5 h-1.5 {{ $isSubActive ? 'bg-pink-600' : 'bg-gray-300' }} rounded-full group-hover:bg-pink-600"></span>
                                    {{ $sub->name }}
                                </a>

                                @if($sub->children->count() > 0)
                                    <button @click.prevent="subOpen = !subOpen" class="pr-4 py-2 text-gray-400 hover:text-pink-600">
                                        <svg :class="subOpen ? 'rotate-90' : ''" class="w-3.5 h-3.5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                @endif
                            </div>

                            <div x-show="subOpen" x-cloak x-collapse class="bg-white">
                                @foreach($sub->children as $child)
                                    @php $isChildActive = request()->is('category/' . $child->slug); @endphp
                                    <div class="menu-row">
                                        <a href="{{ route('category.show', $child->slug) }}" 
                                        class="block pl-16 py-2 menu-text {{ $isChildActive ? 'text-pink-600 font-bold border-l-2 border-pink-600' : 'text-gray-500' }} hover:text-pink-600 transition-all">
                                            {{ $child->name }}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>
</aside>