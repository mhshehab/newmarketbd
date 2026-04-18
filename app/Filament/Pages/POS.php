<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class POS extends Page
{   
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static string $view = 'filament.pages.p-o-s';
    protected static ?string $navigationGroup = 'Sales';
    
    public $customer_id = null;
    public $selectedLanguage = 'en';
    public $cart = [];
    public $search = ''; 
    public $customer_search = '';
    public $barcode_not_found = false;
    // ডিসকাউন্ট প্রপার্টি যুক্ত করা হলো
    public $discount = 0;
    public $discount_type = 'fixed';

    public function mount()
    {
        $this->customer_id = null;
        $this->selectedLanguage = 'en';
        $this->cart = [];
        $this->search = '';
        $this->customer_search = '';
        $this->discount = 0;
        $this->discount_type = 'fixed';
    }

    // কার্টের মোট টাকা হিসাব করার জন্য কম্পিউটেড প্রপার্টি
    public function getTotalProperty()
    {
        $subtotal = collect($this->cart)->sum(fn($i) => $i['price'] * $i['quantity']);

        if ($this->discount_type === 'percent') {
            $discountAmount = $subtotal * ((float) $this->discount / 100);
            return $subtotal - $discountAmount;
        }

        return $subtotal - (float) $this->discount;
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            Notification::make()->title('পণ্যটি পাওয়া যায়নি।')->danger()->send();
            return;
        }

        if ($product->expiry_date && $product->expiry_date < now()) {
            Notification::make()
                ->title('সতর্কতা!')
                ->body('এই পণ্যটির মেয়াদ শেষ হয়ে গেছে।')
                ->danger()
                ->send();
            return;
        }

        if ($product->stock_quantity <= 0) {
            Notification::make()->title('স্টক নেই!')->danger()->send();
            return;
        }

        if (isset($this->cart[$productId])) {
            if ($this->cart[$productId]['quantity'] < $product->stock_quantity) {
                $this->cart[$productId]['quantity']++;
            } else {
                Notification::make()->title('স্টক লিমিট শেষ!')->warning()->send();
            }
        } else {
            $this->cart[$productId] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
            ];
        }
    }

    public function decreaseQuantity($productId)
    {
        if (isset($this->cart[$productId])) {
            if ($this->cart[$productId]['quantity'] > 1) {
                $this->cart[$productId]['quantity']--;
            } else {
                // পরিমাণ ১ এর কম হলে আইটেমটি কার্ট থেকে সরিয়ে ফেলবে
                $this->removeFromCart($productId);
            }
        }
    }

    public function removeFromCart($productId)
    {
        if (isset($this->cart[$productId])) {
            unset($this->cart[$productId]);
            Notification::make()
                ->title('পণ্যটি কার্ট থেকে সরানো হয়েছে')
                ->success()
                ->send();
        }
    }

    public function checkout()
    {
        if (empty($this->cart)) {
            Notification::make()->title('কার্ট খালি!')->warning()->send();
            return;
        }

        if (!$this->customer_id) {
            Notification::make()->title('অনুগ্রহ করে কাস্টমার সিলেক্ট করুন')->danger()->send();
            return;
        }

        // getTotalProperty ব্যবহার করে নিট টোটাল নেওয়া হচ্ছে
        $total = $this->total;

        try {
            $pdfData = DB::transaction(function () use ($total) {
                $currentCart = $this->cart;
                
                foreach ($currentCart as $id => $item) {
                    $product = Product::find($id);
                    if (!$product || $product->stock_quantity < $item['quantity']) {
                        throw new \Exception($product ? "{$product->name} পর্যাপ্ত স্টকে নেই!" : "পণ্যটি খুঁজে পাওয়া যায়নি।");
                    }
                    $product->decrement('stock_quantity', $item['quantity']);
                }

                $order = Order::create([
                    'user_id'      => $this->customer_id,
                    'total_amount' => $total,
                    'status'       => 'delivered',
                    // প্রয়োজনে ডিসকাউন্ট কলাম ডেটাবেসে থাকলে এখানে যোগ করতে পারেন
                ]);

                foreach ($currentCart as $id => $item) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $id,
                        'quantity'   => $item['quantity'],
                        'unit_price' => $item['price'], 
                    ]);
                }

                $pdf = Pdf::loadView('invoices.pos-invoice', [
                    'order' => $order,
                    'cart'  => $currentCart,
                    'lang'  => $this->selectedLanguage,
                    'discount' => $this->discount
                ])->setPaper([0, 0, 226.77, 600], 'portrait')
                  ->setOptions([
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => true,
                        'defaultFont' => 'SolaimanLipi'
                    ]);

                return [
                    'content' => $pdf->output(),
                    'filename' => "invoice-{$order->order_number}.pdf"
                ];
            });

            $this->cart = [];
            $this->customer_id = null;
            $this->discount = 0;
            Notification::make()->title('অর্ডার সফল হয়েছে!')->success()->send();

            return response()->streamDownload(function () use ($pdfData) {
                echo $pdfData['content'];
            }, $pdfData['filename']);

        } catch (\Exception $e) {
            Notification::make()
                ->title('ত্রুটি ঘটেছে')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getCustomers()
    {
        return \App\Models\User::query()
            ->where('name', 'like', "%{$this->customer_search}%")
            ->orWhere('phone', 'like', "%{$this->customer_search}%")
            ->limit(10)
            ->get();
    }

    public function selectCustomer($id, $name)
    {
        $this->customer_id = $id;
        $this->customer_search = $name;
        $this->barcode_not_found = false;
    }

    public function updatedSearch($value)
    {
        $barcode = trim((string) $value);
        $this->barcode_not_found = false;

        if ($barcode === '') {
            return;
        }

        if (!preg_match('/^\d+$/', $barcode) || strlen($barcode) < 3) {
            return;
        }

        $product = Product::where('barcode', $barcode)->first();

        if ($product) {
            $this->addToCart($product->id);
            $this->search = '';
        } else {
            $this->barcode_not_found = true;
        }
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->barcode_not_found = false;
    }

    public function getProducts()
    {
        return \App\Models\Product::query()
            ->where('name', 'like', "%{$this->search}%")
            ->orWhere('barcode', $this->search)
            ->get();
    }
}