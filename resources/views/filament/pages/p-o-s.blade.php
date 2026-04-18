<x-filament-panels::page>
    <div class="grid grid-cols-12 gap-4" x-data="posSystem()">
        
        <div class="col-span-12 flex justify-between items-center bg-white dark:bg-gray-800 p-3 rounded-xl shadow-sm mb-2">
            <div class="flex items-center gap-4">
                <!-- Offline Status Indicator -->
                <div class="flex items-center gap-2" x-data="{ isOnline: navigator.onLine }" x-init="window.addEventListener('online', () => isOnline = true); window.addEventListener('offline', () => isOnline = false);">
                    <div class="w-3 h-3 rounded-full" :class="isOnline ? 'bg-green-500' : 'bg-red-500 animate-pulse'"></div>
                    <span class="text-sm font-bold" :class="isOnline ? 'text-green-600' : 'text-red-600'" x-text="isOnline ? 'Online' : 'Offline'"></span>
                </div>
                
                <div class="flex items-center gap-2">
                    <label for="lang" class="font-bold text-sm">ভাষা:</label>
                    <select wire:model.live="selectedLanguage" id="lang" class="border rounded p-1 text-sm bg-white dark:bg-gray-700">
                        <option value="en">English</option>
                        <option value="bn">বাংলা</option>
                    </select>
                </div>

                <div class="min-w-[300px] relative" x-data="{ open: false }">
                    <input type="text" 
                           wire:model.live="customer_search" 
                           placeholder="নাম বা মোবাইল নাম্বার লিখুন..." 
                           class="border rounded p-2 w-full text-sm dark:bg-gray-700 focus:ring-primary-500"
                           @click="open = true"
                           @click.away="open = false">

                    <div x-show="open && $wire.customer_search.length > 0" 
                         class="absolute z-50 w-full bg-white dark:bg-gray-800 border rounded-b-lg shadow-xl mt-1 max-h-60 overflow-y-auto">
                        @forelse($this->getCustomers() as $customer)
                            <div wire:click="selectCustomer({{ $customer->id }}, '{{ str_replace("'", "\\'", $customer->name) }}')" 
                                 class="p-2 hover:bg-primary-50 dark:hover:bg-gray-700 cursor-pointer border-b last:border-0">
                                <p class="font-bold text-sm">{{ $customer->name }}</p>
                                <p class="text-xs text-gray-500">{{ $customer->phone ?? 'নাম্বার নেই' }}</p>
                            </div>
                        @empty
                            <p class="p-2 text-xs text-gray-400">কোনো কাস্টমার পাওয়া যায়নি</p>
                        @endforelse
                    </div>
                    
                    @if($customer_id)
                        <div class="mt-1 text-[10px] text-success-600 font-bold">
                            সিলেক্টেড: {{ \App\Models\User::find($customer_id)?->name }}
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-400 italic">অটো-আপডেট হচ্ছে...</span>
                <div class="flex flex-col relative">
                    <input type="text" placeholder="পণ্য টাইপ করুন বা বারকোড স্ক্যান করুন..." 
                           autocomplete="off"
                           class="border rounded px-3 py-1 dark:bg-gray-700 focus:ring-primary-500 w-64" 
                           wire:model.live="search">
                    @if($search)
                        <button type="button" wire:click="clearSearch"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            ক্লিয়ার
                        </button>
                    @endif
                    <p class="text-[10px] text-gray-500 mt-1">বারকোড স্ক্যান করলে পণ্যটি স্বয়ংক্রিয়ভাবে কার্টে যোগ হবে।</p>
                    @if($barcode_not_found)
                        <p class="text-[11px] text-red-600 mt-1">বারকোড পাওয়া যায়নি। সঠিক কোড বা ম্যানুয়ালি সার্চ করুন।</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-span-8 bg-white dark:bg-gray-800 p-4 rounded-xl shadow h-[75vh] overflow-y-auto">
            <h2 class="text-xl font-bold mb-4">পণ্য তালিকা</h2>

            <div class="grid grid-cols-3 gap-4" wire:poll.5s> 
                @foreach($this->getProducts() as $product)
                <div class="border p-3 rounded-lg hover:bg-primary-50 dark:hover:bg-gray-700 cursor-pointer transition relative flex flex-col justify-between
                    {{ $product->stock_quantity <= $product->low_stock_threshold ? 'bg-red-50 border-red-400 dark:bg-red-900/20 dark:border-red-800' : 'dark:border-gray-700 bg-white dark:bg-gray-800' }}" 
                     wire:click="addToCart({{ $product->id }})">
                    
                    <div>
                        <h3 class="font-semibold text-sm">{{ $product->name }}</h3>
                        <p class="text-primary-600 font-bold">৳{{ $product->price }}</p>
                    </div>
                    
                    <div class="mt-2 flex flex-col gap-1">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] px-2 py-0.5 rounded {{ $product->stock_quantity <= $product->low_stock_threshold ? 'bg-red-600 text-white' : 'bg-success-600 text-white' }}">
                                স্টক: {{ $product->stock_quantity }}
                            </span>
                            
                            @if($product->stock_quantity <= $product->low_stock_threshold && $product->stock_quantity > 0)
                                <span class="text-[9px] text-red-600 font-bold animate-pulse">Low Stock!</span>
                            @endif
                        </div>

                        <p class="text-[10px] text-gray-500">
                            মেয়াদ: <span class="{{ ($product->expiry_date && \Carbon\Carbon::parse($product->expiry_date)->isPast()) ? 'text-red-500 font-bold' : '' }}">
                                {{ $product->expiry_date ? \Carbon\Carbon::parse($product->expiry_date)->format('d M, Y') : 'N/A' }}
                            </span>
                        </p>
                    </div>

                    @if($product->stock_quantity <= 0)
                        <div class="absolute inset-0 bg-gray-100/60 dark:bg-gray-900/60 flex items-center justify-center rounded-lg z-10">
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
                @forelse($this->cart as $id => $item)
                <div class="flex justify-between items-center mb-3 bg-gray-50 dark:bg-gray-700 p-2 rounded shadow-sm border-l-4 border-primary-500">
                    <div class="flex-grow">
                        <p class="font-medium text-sm">{{ $item['name'] }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <button wire:click="decreaseQuantity({{ $id }})" 
                                    class="bg-gray-200 dark:bg-gray-600 px-2 py-0.5 rounded text-sm hover:bg-gray-300 transition">-</button>
                            
                            <span class="font-bold text-xs">{{ $item['quantity'] }}</span>
                            
                            <button wire:click="addToCart({{ $id }})" 
                                    class="bg-gray-200 dark:bg-gray-600 px-2 py-0.5 rounded text-sm hover:bg-gray-300 transition">+</button>
                            
                            <span class="text-[10px] text-gray-500 ml-1">x ৳{{ $item['price'] }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <p class="font-bold text-sm text-primary-600">৳{{ $item['quantity'] * $item['price'] }}</p>
                        <button wire:click="removeFromCart({{ $id }})" class="text-red-500 hover:text-red-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
                @empty
                <div class="text-center mt-10">
                    <p class="text-gray-400 italic">কার্ট খালি</p>
                </div>
                @endforelse
            </div>

            <div class="border-t pt-4 mt-auto space-y-3 bg-gray-50 dark:bg-gray-800 p-4 rounded-b-xl">
                
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                    <span>সাবটোটাল:</span>
                    <span>Tk{{ collect($this->cart)->sum(fn($i) => $i['price'] * $i['quantity']) }}</span>
                </div>

                <!-- Weighing Scale Display -->
                @if($this->selected_weighted_product)
                <div class="flex items-center justify-between gap-2 p-2 bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800 rounded-lg shadow-sm">
                    <div class="flex flex-col">
                        <label class="text-[10px] font-bold text-green-700 dark:text-green-300 uppercase">Weighing Scale</label>
                        <span class="text-xs font-semibold">{{ \App\Models\Product::find($this->selected_weighted_product)?->name }}</span>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-lg font-bold text-green-600">{{ $this->current_weight }} kg</span>
                        <button wire:click="addWeightedProductToCart" class="text-xs bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700 transition">
                            Add to Cart
                        </button>
                    </div>
                </div>
                @endif

                <!-- Loyalty Points -->
                @if($this->customer_id && $this->getCustomerLoyaltyPoints() > 0)
                <div class="flex items-center justify-between gap-2 p-2 bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 rounded-lg shadow-sm">
                    <div class="flex flex-col">
                        <label class="text-[10px] font-bold text-purple-700 dark:text-purple-300 uppercase">Loyalty Points</label>
                        <span class="text-xs">Available: {{ $this->getCustomerLoyaltyPoints() }} pts</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model.live="use_loyalty_points" class="rounded">
                        @if($this->use_loyalty_points)
                        <input type="number" 
                               wire:model.live="points_to_redeem" 
                               placeholder="0"
                               class="w-16 text-right border rounded text-sm font-bold"
                               min="0"
                               max="{{ $this->getMaxRedeemablePoints() }}">
                        <span class="text-xs text-purple-600">= Tk{{ $this->loyalty_discount }}</span>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Discount Code -->
                <div class="flex items-center justify-between gap-2 p-2 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-lg shadow-sm">
                    <div class="flex flex-col">
                        <label class="text-[10px] font-bold text-indigo-700 dark:text-indigo-300 uppercase">Discount Code</label>
                        <div class="flex items-center gap-2">
                            <input type="text" 
                                   wire:model.live="discount_code" 
                                   placeholder="Enter code"
                                   class="w-32 border rounded bg-white dark:bg-gray-700 text-sm font-bold focus:ring-2 focus:ring-primary-500 py-1">
                            @if($this->discount_code)
                            <button wire:click="applyDiscountCode" class="text-xs bg-indigo-600 text-white px-2 py-1 rounded hover:bg-indigo-700 transition">
                                Apply
                            </button>
                            @endif
                        </div>
                        @if($this->applied_discount)
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-xs text-green-600">{{ $this->applied_discount->name }} applied</span>
                            <button wire:click="removeDiscount" class="text-xs text-red-600 hover:text-red-800">
                                Remove
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Available Offers -->
                @if(count($this->getAvailableDiscountsForCart()) > 0)
                <div class="flex flex-col gap-2 p-2 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-100 dark:border-yellow-800 rounded-lg shadow-sm">
                    <label class="text-[10px] font-bold text-yellow-700 dark:text-yellow-300 uppercase">Available Offers</label>
                    @foreach($this->getAvailableDiscountsForCart() as $offer)
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold">{{ $offer['discount']->name }}</span>
                        <span class="text-yellow-600">Save Tk{{ $offer['amount'] }}</span>
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Payment Method Selection -->
                <div class="flex items-center justify-between gap-2 p-2 bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-800 rounded-lg shadow-sm">
                    <div class="flex flex-col">
                        <label class="text-[10px] font-bold text-orange-700 dark:text-orange-300 uppercase">Payment Method</label>
                        <select wire:model.live="payment_method" class="border-none bg-transparent text-xs p-0 focus:ring-0 font-semibold cursor-pointer">
                            @foreach($this->getPaymentMethods() as $key => $method)
                            <option value="{{ $key }}">{{ $method }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    @if($this->payment_method === 'cash')
                    <div class="flex flex-col items-end">
                        <label for="cash_received" class="text-[10px] font-bold text-orange-700 dark:text-orange-300 uppercase">Cash Received</label>
                        <input type="number" 
                               id="cash_received"
                               wire:model.live="cash_received" 
                               placeholder="{{ $this->total }}"
                               class="w-24 text-right border rounded bg-white dark:bg-gray-700 text-sm font-bold focus:ring-2 focus:ring-primary-500 py-1"
                               min="0">
                        @if($this->change_amount > 0)
                        <span class="text-xs text-green-600">Change: Tk{{ $this->change_amount }}</span>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-2 p-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-lg shadow-sm">
                    <div class="flex flex-col">
                        <label class="text-[10px] font-bold text-blue-700 dark:text-blue-300 uppercase">Discount Type</label>
                        <select wire:model.live="discount_type" class="border-none bg-transparent text-xs p-0 focus:ring-0 font-semibold cursor-pointer">
                            <option value="fixed">টাকা (৳)</option>
                            <option value="percent">শতাংশ (%)</option>
                        </select>
                    </div>
                    
                    <div class="flex flex-col items-end">
                        <label for="discount" class="text-[10px] font-bold text-blue-700 dark:text-blue-300 uppercase">পরিমাণ</label>
                        <div class="flex items-center">
                            <input type="number" 
                                   id="discount"
                                   wire:model.live="discount" 
                                   placeholder="0"
                                   class="w-20 text-right border-none rounded bg-white dark:bg-gray-700 text-sm font-bold focus:ring-2 focus:ring-primary-500 py-1"
                                   min="0">
                            <span class="ml-1 font-bold text-sm">
                                {{ $this->discount_type === 'percent' ? '%' : 'Tk' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-2xl font-black text-primary-600 border-t border-dashed pt-2">
                    <span>মোট:</span>
                    {{-- $this->total ব্যবহার করা হয়েছে যা POS.php থেকে ক্যালকুলেট হবে --}}
                    <span>৳{{ number_format($this->total, 2) }}</span>
                </div>
                
                <button wire:click="checkout" 
                        class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-4 rounded-lg shadow-xl active:scale-95 transition-all flex items-center justify-center gap-2 disabled:bg-gray-400 disabled:shadow-none"
                        x-bind:disabled="!isOnline || {{ count($this->cart) === 0 ? 'true' : 'false' }}">
                    
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>

                    <span x-show="isOnline">অর্ডার সম্পন্ন করুন (F2)</span>
                    <span x-show="!isOnline" x-cloak>অফলাইন (পেমেন্ট বন্ধ)</span>
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