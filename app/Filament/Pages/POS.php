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
    
    // ১. প্রপার্টিগুলো সবসময় এখানে (ফাংশনের বাইরে) থাকতে হবে
    public $customer_id = null;
    public $selectedLanguage = 'en';
    public $cart = [];
    public $search = ''; 

    // ২. মাউন্ট ফাংশনে শুধুমাত্র ভ্যালু এসাইন করা হয়
    public function mount()
    {
        $this->customer_id = null;
        $this->selectedLanguage = 'en';
        $this->cart = [];
        $this->search = '';
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);

        if (!$product || $product->stock_quantity <= 0) {
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

        $currentCart = $this->cart;
        $total = collect($currentCart)->sum(fn($i) => $i['price'] * $i['quantity']);

        try {
            $pdfData = DB::transaction(function () use ($total, $currentCart) {
                
                // স্টক চেক ও আপডেট
                foreach ($currentCart as $id => $item) {
                    $product = Product::find($id);
                    if (!$product || $product->stock_quantity < $item['quantity']) {
                        throw new \Exception($product ? "{$product->name} পর্যাপ্ত স্টকে নেই!" : "পণ্যটি খুঁজে পাওয়া যায়নি।");
                    }
                    $product->decrement('stock_quantity', $item['quantity']);
                }

                // অর্ডার তৈরি
                $order = Order::create([
                    'user_id'      => $this->customer_id,
                    'total_amount' => $total,
                    'status'       => 'delivered',
                ]);

                // অর্ডার আইটেম সেভ করা
                foreach ($currentCart as $id => $item) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $id,
                        'quantity'   => $item['quantity'],
                        'unit_price' => $item['price'], 
                    ]);
                }

                // PDF জেনারেশন
                $pdf = Pdf::loadView('invoices.pos-invoice', [
                    'order' => $order,
                    'cart'  => $currentCart,
                    'lang'  => $this->selectedLanguage
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
        return User::all();
    }

    public function getProducts()
    {
        return Product::query()
            ->where('name', 'like', "%{$this->search}%")
            ->get();
    }
}