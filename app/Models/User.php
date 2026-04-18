<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'role',
        'loyalty_points',
        'loyalty_points_expire_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'loyalty_points_expire_at' => 'date',
        ];
    }

    public function loyaltyPoints()
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getTotalSpentAttribute()
    {
        return $this->orders()->where('status', 'delivered')->sum('total_amount');
    }

    public function canRedeemPoints($points)
    {
        return $this->loyalty_points >= $points;
    }

    public function getAvailablePointsValue()
    {
        return LoyaltyPoint::getPointsValue($this->loyalty_points);
    }
}
