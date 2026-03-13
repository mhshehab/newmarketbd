<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // ইউনিট এবং ডিসকাউন্ট কলাম যোগ করা হচ্ছে
            $table->string('unit')->nullable()->after('name'); 
            $table->decimal('discount_price', 10, 2)->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['unit', 'discount_price']);
        });
    }
};