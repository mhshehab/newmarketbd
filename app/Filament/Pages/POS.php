<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Product;
use Livewire\Attributes\On;

class POS extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static string $view = 'filament.pages.p-o-s';
    protected static ?string $title = 'Point of Sale';

    public $search = '';
    public $cart = [];
    public $total = 0;

    // বারকোড বা নাম দিয়ে সার্চ করলে এই ফাংশনটি কাজ করবে
    public function updatedSearch()
    {
        $product = Product::where('barcode', $this->search)
                        ->orWhere('name', 'like', '%' . $this->search . '%')
                        ->first();

        if ($product) {
            $this->addToCart($product->id);
            $this->search = ''; // সার্চ বক্স খালি করে দেওয়া যাতে পরের বারকোড নেওয়া যায়
        }
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty']++;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'qty' => 1,
            ];
        }
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total = collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
    }

    public function checkout()
    {
        // এখানে ডাটাবেসে সেভ করার এবং ইনভেন্টরি মাইনাস করার লজিক হবে
        // এবং শেষে থার্মাল প্রিন্টারের জন্য উইন্ডো ওপেন হবে
    }
}