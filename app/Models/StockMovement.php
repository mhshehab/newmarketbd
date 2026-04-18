<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type', // 'sale', 'purchase', 'adjustment', 'return'
        'quantity',
        'reference_id', // order_id, purchase_id, etc.
        'reference_type', // Order, Purchase, etc.
        'notes',
        'user_id', // who performed the action
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Real-time stock update
    public static function recordMovement($productId, $type, $quantity, $referenceId = null, $referenceType = null, $notes = null, $userId = null)
    {
        $movement = self::create([
            'product_id' => $productId,
            'type' => $type,
            'quantity' => $quantity,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'notes' => $notes,
            'user_id' => $userId ?? auth()->id(),
        ]);

        // Update product stock in real-time
        $product = Product::find($productId);
        if ($product) {
            if ($type === 'sale' || $type === 'return_from_customer') {
                $product->decrement('stock_quantity', abs($quantity));
            } elseif ($type === 'purchase' || $type === 'return_to_supplier' || $type === 'adjustment') {
                $product->increment('stock_quantity', abs($quantity));
            }

            // Check for low stock alert
            if ($product->stock_quantity <= $product->low_stock_threshold) {
                self::sendLowStockAlert($product);
            }
        }

        return $movement;
    }

    public static function sendLowStockAlert($product)
    {
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            \Filament\Notifications\Notification::make()
                ->title('Low Stock Alert!')
                ->body("Product: {$product->name} - Stock: {$product->stock_quantity}")
                ->warning()
                ->icon('heroicon-o-exclamation-triangle')
                ->actions([
                    \Filament\Notifications\Actions\Action::make('restock')
                        ->label('Restock Now')
                        ->url("/admin/products/{$product->id}/edit")
                        ->button(),
                ])
                ->sendToDatabase($admin);
        }
    }
}
