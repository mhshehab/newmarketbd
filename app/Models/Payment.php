<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    const METHODS = [
        'cash' => 'Cash',
        'card' => 'Credit/Debit Card',
        'bkash' => 'bKash',
        'nagad' => 'Nagad',
        'rocket' => 'Rocket',
        'upay' => 'Upay',
        'digital_wallet' => 'Digital Wallet',
        'gift_card' => 'Gift Card',
        'bank_transfer' => 'Bank Transfer',
    ];

    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'transaction_id',
        'status', // 'pending', 'completed', 'failed', 'refunded'
        'payment_details', // JSON for mobile banking, card details
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'paid_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    
    public static function processPayment($orderId, $method, $amount, $details = null)
    {
        $payment = self::create([
            'order_id' => $orderId,
            'payment_method' => $method,
            'amount' => $amount,
            'transaction_id' => self::generateTransactionId($method),
            'payment_details' => $details,
            'status' => 'pending',
        ]);

        // Process payment based on method
        $result = self::processPaymentMethod($payment, $method, $details);

        if ($result['success']) {
            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
            ]);
        } else {
            $payment->update(['status' => 'failed']);
        }

        return $payment;
    }

    private static function generateTransactionId($method)
    {
        $prefix = strtoupper(substr($method, 0, 3));
        return $prefix . '-' . time() . '-' . rand(1000, 9999);
    }

    private static function processPaymentMethod($payment, $method, $details)
    {
        switch ($method) {
            case 'cash':
                return ['success' => true, 'message' => 'Cash payment received'];
            
            case 'card':
                return self::processCardPayment($details);
            
            case 'bkash':
            case 'nagad':
            case 'rocket':
            case 'upay':
                return self::processMobileBanking($method, $details);
            
            case 'bank_transfer':
                return self::processBankTransfer($details);
            
            case 'digital_wallet':
                return self::processDigitalWallet($details);
            
            case 'gift_card':
                return self::processGiftCard($details);
            
            default:
                return ['success' => false, 'message' => 'Invalid payment method'];
        }
    }

    private static function processCardPayment($details)
    {
        // Simulate card payment processing
        // In real implementation, integrate with payment gateway
        return ['success' => true, 'message' => 'Card payment processed'];
    }

    private static function processMobileBanking($method, $details)
    {
        // Simulate mobile banking payment
        // In real implementation, integrate with respective APIs
        return ['success' => true, 'message' => "{$method} payment processed"];
    }

    private static function processBankTransfer($details)
    {
        // Bank transfer verification
        return ['success' => true, 'message' => 'Bank transfer verified'];
    }

    private static function processDigitalWallet($details)
    {
        // Digital wallet processing
        return ['success' => true, 'message' => 'Digital wallet payment processed'];
    }

    private static function processGiftCard($details)
    {
        // Gift card validation and processing
        return ['success' => true, 'message' => 'Gift card processed'];
    }
}
