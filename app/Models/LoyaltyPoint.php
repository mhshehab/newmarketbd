<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoyaltyPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'points_earned',
        'points_redeemed',
        'balance',
        'transaction_type', // 'earned', 'redeemed'
        'description',
        'expires_at',
    ];

    protected $casts = [
        'points_earned' => 'integer',
        'points_redeemed' => 'integer',
        'balance' => 'integer',
        'expires_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public static function earnPoints($userId, $orderId, $amount, $description = null)
    {
        $points = self::calculatePoints($amount);
        $currentBalance = self::getCurrentBalance($userId);
        
        $loyaltyPoint = self::create([
            'user_id' => $userId,
            'order_id' => $orderId,
            'points_earned' => $points,
            'points_redeemed' => 0,
            'balance' => $currentBalance + $points,
            'transaction_type' => 'earned',
            'description' => $description ?? "Earned from order #{$orderId}",
            'expires_at' => now()->addYear(), // Points expire after 1 year
        ]);

        // Update user's total loyalty points
        $user = User::find($userId);
        if ($user) {
            $user->update(['loyalty_points' => $currentBalance + $points]);
        }

        return $loyaltyPoint;
    }

    public static function redeemPoints($userId, $pointsToRedeem, $description = null)
    {
        $currentBalance = self::getCurrentBalance($userId);
        
        if ($currentBalance < $pointsToRedeem) {
            return false;
        }

        $loyaltyPoint = self::create([
            'user_id' => $userId,
            'order_id' => null,
            'points_earned' => 0,
            'points_redeemed' => $pointsToRedeem,
            'balance' => $currentBalance - $pointsToRedeem,
            'transaction_type' => 'redeemed',
            'description' => $description ?? "Redeemed {$pointsToRedeem} points",
            'expires_at' => null,
        ]);

        // Update user's total loyalty points
        $user = User::find($userId);
        if ($user) {
            $user->update(['loyalty_points' => $currentBalance - $pointsToRedeem]);
        }

        return $loyaltyPoint;
    }

    public static function calculatePoints($amount)
    {
        // 1 point per 10 Taka spent
        return (int) ($amount / 10);
    }

    public static function getCurrentBalance($userId)
    {
        return self::where('user_id', $userId)
            ->sum('points_earned') - self::where('user_id', $userId)
            ->sum('points_redeemed');
    }

    public static function getPointsValue($points)
    {
        // 100 points = 10 Taka discount
        return ($points / 100) * 10;
    }

    public static function expireOldPoints()
    {
        $expiredPoints = self::where('expires_at', '<', now())
            ->where('balance', '>', 0)
            ->get();

        foreach ($expiredPoints as $point) {
            $user = $point->user;
            if ($user) {
                $newBalance = max(0, $user->loyalty_points - $point->balance);
                $user->update(['loyalty_points' => $newBalance]);
            }
            
            $point->update(['balance' => 0]);
        }

        return $expiredPoints->count();
    }
}
