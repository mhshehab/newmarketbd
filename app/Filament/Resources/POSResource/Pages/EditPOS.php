<?php

namespace App\Filament\Resources\POSResource\Pages;

use App\Filament\Resources\POSResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class EditPOS extends EditRecord
{
    protected static string $resource = POSResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Delete Order')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'pending'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Only allow editing if order is pending
        if ($this->record->status !== 'pending') {
            Notification::make()
                ->title('Cannot Edit Order')
                ->body('Only pending orders can be edited.')
                ->danger()
                ->send();
            
            $this->halt();
        }

        // Recalculate totals
        $subtotal = 0;
        $items = [];

        foreach ($data['items'] as $item) {
            $product = \App\Models\Product::find($item['product_id']);
            if ($product) {
                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'];
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

    protected function afterSave(): void
    {
        $order = $this->record;
        $data = $this->form->getState();

        DB::beginTransaction();
        try {
            // Delete existing order items and restore stock
            foreach ($order->orderItems as $existingItem) {
                // Restore stock
                $product = \App\Models\Product::find($existingItem->product_id);
                if ($product) {
                    $product->increment('stock_quantity', $existingItem->quantity);
                    
                    // Create stock movement for restoration
                    \App\Models\StockMovement::create([
                        'product_id' => $product->id,
                        'movement_type' => 'adjustment',
                        'quantity' => $existingItem->quantity,
                        'reference_id' => $order->id,
                        'reference_type' => 'order',
                        'user_id' => auth()->id(),
                        'notes' => "Stock restored from order edit #{$order->order_number}",
                    ]);
                }
                
                $existingItem->delete();
            }

            // Create new order items
            foreach ($data['_calculated_items'] as $item) {
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);

                // Update stock
                $product = \App\Models\Product::find($item['product_id']);
                if ($product) {
                    $product->decrement('stock_quantity', $item['quantity']);
                    
                    // Create stock movement
                    \App\Models\StockMovement::create([
                        'product_id' => $product->id,
                        'movement_type' => 'sale',
                        'quantity' => -$item['quantity'],
                        'reference_id' => $order->id,
                        'reference_type' => 'order',
                        'user_id' => auth()->id(),
                        'notes' => "Sale via edited POS Order #{$order->order_number}",
                    ]);
                }
            }

            // Update payment record
            $payment = $order->payments()->first();
            if ($payment) {
                $payment->update([
                    'payment_method' => $data['payment_method'],
                    'amount' => $order->total_amount,
                    'payment_details' => [
                        'cash_received' => $data['cash_received'] ?? null,
                        'change_amount' => $data['change_amount'] ?? 0,
                        'payment_notes' => $data['payment_notes'] ?? null,
                    ],
                ]);
            }

            // Update loyalty points
            $order->loyaltyPoints()->delete();
            
            // Handle redeemed points
            if (isset($data['points_to_redeem']) && $data['points_to_redeem'] > 0) {
                \App\Models\LoyaltyPoint::create([
                    'user_id' => $data['user_id'],
                    'order_id' => $order->id,
                    'transaction_type' => 'redeemed',
                    'points_redeemed' => $data['points_to_redeem'],
                    'points_earned' => 0,
                    'description' => "Redeemed points for edited order #{$order->order_number}",
                ]);
            }

            // Earn loyalty points (1 point per 10 Tk spent)
            $pointsEarned = intval($order->total_amount / 10);
            if ($pointsEarned > 0) {
                \App\Models\LoyaltyPoint::create([
                    'user_id' => $data['user_id'],
                    'order_id' => $order->id,
                    'transaction_type' => 'earned',
                    'points_earned' => $pointsEarned,
                    'points_redeemed' => 0,
                    'description' => "Earned points from edited order #{$order->order_number}",
                ]);
            }

            DB::commit();

            Notification::make()
                ->title('Order Updated Successfully')
                ->body("POS Order #{$order->order_number} has been updated")
                ->success()
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Notification::make()
                ->title('Order Update Failed')
                ->body('There was an error updating the order. Please try again.')
                ->danger()
                ->send();
            
            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getFormActions(): array
    {
        return [
            Actions\SaveAction::make()
                ->label('Update Order')
                ->icon('heroicon-o-check')
                ->color('primary'),
                
            Actions\Action::make('cancel')
                ->label('Cancel')
                ->url($this->getResource()::getUrl('view', ['record' => $this->record]))
                ->color('gray'),
        ];
    }
}
