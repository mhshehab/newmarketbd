<x-filament-panels::page>
    <div class="grid grid-cols-12 gap-4" x-data="posSystem()">
        
        <div class="flex items-center gap-2">
            <label for="lang" class="font-bold">ভাষা নির্বাচন করুন:</label>
            <select wire:model.live="selectedLanguage" id="lang" class="border rounded p-1 text-sm bg-white">
                <option value="en">English</option>
                <option value="bn">বাংলা</option>
            </select>
        </div>

        <div>
            <select wire:model.live="customer_id" class="border rounded p-1 w-full text-sm">
                <option value="">কাস্টমার সিলেক্ট করুন</option>
                @foreach($this->getCustomers() as $customer)
                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="col-span-8 bg-white dark:bg-gray-800 p-4 rounded-xl shadow">
            <div class="flex justify-between mb-4 items-center">
                <h2 class="text-xl font-bold">পণ্য তালিকা</h2>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400">অটো-আপডেট হচ্ছে...</span>
                    <input type="text" placeholder="পণ্য খুঁজুন..." 
                           class="border rounded px-3 py-1 dark:bg-gray-700 focus:ring-primary-500" 
                           wire:model.live="search">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4" wire:poll.3s> 
                @foreach($this->getProducts() as $product)
                <div class="border dark:border-gray-700 p-3 rounded-lg hover:bg-primary-50 dark:hover:bg-gray-700 cursor-pointer transition relative" 
                     wire:click="addToCart({{ $product->id }})">
                    
                    <h3 class="font-semibold">{{ $product->name }}</h3>
                    <p class="text-primary-600 font-bold">৳{{ $product->price }}</p>
                    
                    <div class="mt-2">
                        <span class="text-xs px-2 py-1 rounded {{ $product->stock_quantity <= 0 ? 'bg-danger-600 text-white' : 'bg-success-600 text-white' }}">
                            স্টক: {{ $product->stock_quantity }}
                        </span>
                    </div>

                    @if($product->stock_quantity <= 0)
                        <div class="absolute inset-0 bg-gray-100/50 dark:bg-gray-900/50 flex items-center justify-center rounded-lg">
                            <span class="bg-red-600 text-white text-[10px] px-2 py-1 rounded uppercase font-bold shadow-sm">Stock Out</span>
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <div class="col-span-4 bg-white dark:bg-gray-800 p-4 rounded-xl shadow flex flex-col h-[75vh]">
            <h2 class="text-xl font-bold mb-4 border-b pb-2">কার্ট (অর্ডার)</h2>
            
            <div class="flex-grow overflow-y-auto pr-2">
                @forelse($cart as $id => $item)
                <div class="flex justify-between items-center mb-4 bg-gray-50 dark:bg-gray-700 p-2 rounded shadow-sm border-l-4 border-primary-500">
                    <div>
                        <p class="font-medium text-sm">{{ $item['name'] }}</p>
                        <p class="text-[10px] text-gray-500">{{ $item['quantity'] }} x ৳{{ $item['price'] }}</p>
                    </div>
                    <p class="font-bold text-sm text-primary-600">৳{{ $item['quantity'] * $item['price'] }}</p>
                </div>
                @empty
                <div class="text-center mt-10">
                    <p class="text-gray-400 italic">কার্ট খালি</p>
                </div>
                @endforelse
            </div>

            <div class="border-t pt-4 mt-auto">
                <div class="flex justify-between text-xl font-black mb-4 text-primary-600">
                    <span>মোট:</span>
                    <span>৳{{ collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']) }}</span>
                </div>
                
                <button wire:click="checkout" 
                        class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-4 rounded-lg shadow-lg active:scale-95 transition-all disabled:bg-gray-400"
                        x-bind:disabled="!isOnline">
                    <span x-show="isOnline">অর্ডার সম্পন্ন করুন (F2)</span>
                    <span x-show="!isOnline" x-cloak>অফলাইনে আছেন</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function posSystem() {
            return {
                isOnline: navigator.onLine,
                init() {
                    window.addEventListener('online', () => { this.isOnline = true; });
                    window.addEventListener('offline', () => { this.isOnline = false; });
                    
                    window.addEventListener('keydown', (e) => {
                        if (e.key === 'F2') {
                            @this.checkout();
                        }
                    });
                }
            }
        }
    </script>
</x-filament-panels::page>