<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description', 'is_public', 'position'];

    protected $casts = [
        'is_public' => 'boolean',
        'position' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        // ডাটা সেভ বা ডিলিট হলে ক্যাশ ক্লিয়ার হবে
        static::saved(fn ($setting) => Cache::forget("settings.{$setting->key}"));
        static::deleted(fn ($setting) => Cache::forget("settings.{$setting->key}"));
    }

    // ফ্রন্টএন্ডে ডাটা পাওয়ার জন্য সহজ মেথড
    public static function getValue($key, $default = null)
    {
        return Cache::rememberForever("settings.{$key}", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            if (!$setting) return $default;
            
            // ডাটাবেস লেভেল প্রোটেকশন: ইমেজ কী-এর জন্য কখনই boolean রিটার্ন করবে না
            if ($setting->type === 'image' && is_bool($setting->value)) {
                return $default;
            }
            
            return $setting->value;
        });
    }

    // এই মেথডটি নিশ্চিত করে যে ইমেজ ডাটা সবসময় অ্যারে হিসেবে রিটার্ন হবে
    public function getFilamentValue()
    {
        if ($this->type === 'image') {
            // Boolean, "1", empty string, null, false সব খালি অ্যারে করে দাও
            if (is_bool($this->value) || $this->value === "1" || $this->value === "" || $this->value === null || $this->value === false) {
                return [];
            }
            return is_array($this->value) ? $this->value : [$this->value];
        }
        return $this->value;
    }
}
