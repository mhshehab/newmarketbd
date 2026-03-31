<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // parent_id অবশ্যই এখানে থাকতে হবে, নতুবা সাব-ক্যাটাগরি সেভ হবে না
    protected $fillable = ['name', 'slug', 'parent_id', 'image', 'icon'];

    /**
     * প্যারেন্ট ক্যাটাগরি খুঁজে পেতে
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * সরাসরি নিচের লেভেলের সাব-ক্যাটাগরিগুলো খুঁজে পেতে
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * এটি সব লেভেলের চাইল্ড (নেস্টেড ক্যাটাগরি) চেক করতে সাহায্য করে।
     * সাইডবারে ক্যাটাগরি অটো-ওপেন রাখার জন্য এটি প্রয়োজন।
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * আনলিমিটেড নেস্টেড ক্যাটাগরি লোড করার জন্য (Recursive)
     */
    public function subcategories()
    {
        return $this->children()->with('subcategories');
    }

    /**
     * ক্যাটাগরির আন্ডারে থাকা প্রোডাক্টগুলো
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * ফুল নেম অ্যাট্রিবিউট (ব্রেডক্রাম্ব বা নেমিং এর জন্য)
     */
    public function getFullNameAttribute()
    {
        if ($this->parent) {
            return $this->parent->full_name . ' > ' . $this->name;
        }

        return $this->name;
    }
}