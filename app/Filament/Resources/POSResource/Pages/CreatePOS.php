<?php

namespace App\Filament\Resources\POSResource\Pages;

use App\Filament\Resources\POSResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\StockMovement;
use App\Models\LoyaltyPoint;
use App\Models\Discount;

class CreatePOS extends CreateRecord
{
    protected static string $resource = POSResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate order number
        $data['order_number'] = 'POS-' . date('Ymd') . '-' . str_pad(Order::max('id') + 1, 4, '0', STR_PAD_LEFT);
        $data['status'] = 'pending';

        // Calculate totals
        $subtotal = 0;
        $items = [];

        foreach ($data['items'] as $item) {
            $product = \App\Models\Product::find($item['product_id']);
            if ($product) {
                $quantity = $item['quantity'];
                $unitPrice = $product->price;
                $totalPrice = $quantity * $unitPrice;
                
                $subtotal += $totalPrice;
                
                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ];
            }
        }

        // Calculate discounts
        $loyaltyDiscount = 0;
        if (isset($data['points_to_redeem']) && $data['points_to_redeem'] > 0) {
            $loyaltyDiscount = ($data['points_to_redeem'] / 100) * 10; // 100 points = 10 Tk
        }

        $manualDiscount = 0;
        if (isset($data['manual_discount']) && $data['manual_discount'] > 0) {
            $manualDiscount = $data['manual_discount'];
            if (($data['manual_discount_type'] ?? 'fixed') === 'percentage') {
                $manualDiscount = ($subtotal * $manualDiscount) / 100;
            }
        }

        $totalAmount = $subtotal - $loyaltyDiscount - $manualDiscount;
        $data['total_amount'] = max(0, $totalAmount);

        // Store calculated values for later use
        $data['_calculated_items'] = $items;
        $data['_loyalty_discount'] = $loyaltyDiscount;
        $data['_manual_discount'] = $manualDiscount;
        $data['_subtotal'] = $subtotal;

        return $data;
    }

    protected function afterCreate(): void
    {
        $order = $this->record;
        $data = $this->form->getState();

        DB::beginTransaction();
        try {
            // Create order items
            foreach ($data['items'] as $item) {
                $product = \App\Models\Product::find($item['product_id']);
                if ($product) {
                    $quantity = $item['quantity'];
                    $unitPrice = $product->price;
                    $totalPrice = $quantity * $unitPrice;
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                    ]);

                    // Update stock
                    $product->decrement('stock_quantity', $item['quantity']);
                    
                    // Create stock movement
                    StockMovement::create([
                        'product_id' => $product->id,
                        'movement_type' => 'sale',
                        'quantity' => -$item['quantity'],
                        'reference_id' => $order->id,
                        'reference_type' => 'order',
                        'user_id' => auth()->id(),
                        'notes' => "Sale via POS Order #{$order->order_number}",
                    ]);
                }
            }

            // Create payment record
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $data['payment_method'],
                'amount' => $order->total_amount,
                'status' => 'completed',
                'payment_details' => [
                    'cash_received' => $data['cash_received'] ?? null,
                    'change_amount' => $data['change_amount'] ?? 0,
                    'payment_notes' => $data['payment_notes'] ?? null,
                ],
                'paid_at' => now(),
            ]);

            // Handle loyalty points
            if (isset($data['points_to_redeem']) && $data['points_to_redeem'] > 0) {
                LoyaltyPoint::create([
                    'user_id' => $data['user_id'],
                    'order_id' => $order->id,
                    'transaction_type' => 'redeemed',
                    'points_redeemed' => $data['points_to_redeem'],
                    'points_earned' => 0,
                    'description' => "Redeemed points for order #{$order->order_number}",
                ]);
            }

            // Earn loyalty points (1 point per 10 Tk spent)
            $pointsEarned = intval($order->total_amount / 10);
            if ($pointsEarned > 0) {
                LoyaltyPoint::create([
                    'user_id' => $data['user_id'],
                    'order_id' => $order->id,
                    'transaction_type' => 'earned',
                    'points_earned' => $pointsEarned,
                    'points_redeemed' => 0,
                    'description' => "Earned points from order #{$order->order_number}",
                ]);
            }

            DB::commit();

            Notification::make()
                ->title('Order Created Successfully')
                ->body("POS Order #{$order->order_number} has been created with total amount of  {$order->total_amount} BDT")
                ->success()
                ->send();
            
            // Redirect to order view page with invoice download
            $this->redirect($this->getResource()::getUrl('view', ['record' => $order->id]));

        } catch (\Exception $e) {
            DB::rollBack();
            
            Notification::make()
                ->title('Order Creation Failed')
                ->body('There was an error creating the order. Please try again.')
                ->danger()
                ->send();
            
            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Order')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->submit('create'),
                
            Actions\Action::make('cancel')
                ->label('Cancel')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }
}
