<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    // আমরা কোন কোন ফিল্ডে ডাটা ইনপুট দিতে পারব তা এখানে বলে দিতে হবে
    protected $fillable = ['title', 'image', 'link', 'button_text', 'is_active'];
}
