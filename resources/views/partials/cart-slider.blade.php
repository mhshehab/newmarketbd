<div class="fixed right-0 top-1/2 -translate-y-1/2 z-50 flex flex-col items-center bg-[#5d3289] text-white p-2 rounded-l-md cursor-pointer shadow-2xl hover:bg-[#4a286d] transition-all" @click="cartOpen = true">
    <div class="flex flex-col items-center border-b border-purple-400 pb-1 mb-1">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
        <span class="text-[10px] font-bold uppercase mt-1">2 Items</span>
    </div>
    <div class="bg-white text-[#5d3289] px-2 py-0.5 rounded-sm font-bold text-xs uppercase tracking-tighter">৳ 158</div>
</div>

<div x-show="cartOpen" x-cloak class="fixed inset-0 z-[60] overflow-hidden">
    <div class="absolute inset-0 bg-black/40" @click="cartOpen = false"></div>
    <div class="fixed inset-y-0 right-0 max-w-sm w-full bg-white shadow-2xl flex flex-col transform transition-transform" x-transition:enter="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="translate-x-0" x-transition:leave-end="translate-x-full">
        <div class="p-4 border-b flex justify-between items-center bg-gray-50 font-bold text-gray-700">
            <div class="flex items-center gap-2"><svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg> 2 Items</div>
            <button @click="cartOpen = false" class="text-gray-400 hover:text-black text-2xl">&times;</button>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            <div class="flex gap-3 border-b pb-3">
                <img src="https://via.placeholder.com/60" class="w-12 h-12 rounded bg-gray-100">
                <div class="flex-1">
                    <h5 class="text-sm font-bold">Shagor Kola</h5>
                    <p class="text-xs text-gray-500 italic">৳ 100 / 1 doz</p>
                    <div class="flex items-center gap-3 mt-2">
                        <button class="w-6 h-6 border flex items-center justify-center rounded-full hover:bg-gray-100">-</button>
                        <span class="text-sm font-bold">1</span>
                        <button class="w-6 h-6 border flex items-center justify-center rounded-full bg-pink-600 text-white">+</button>
                    </div>
                </div>
                <div class="text-sm font-bold">৳ 100</div>
            </div>
        </div>
        <div class="p-4 bg-gray-50 border-t">
            <div class="flex justify-between font-bold mb-4 px-2"><span>Total</span><span>৳ 158</span></div>
            <button class="w-full bg-pink-600 text-white py-3 rounded-lg font-extrabold hover:bg-pink-700 shadow-lg">CHECKOUT</button>
        </div>
    </div>
</div>