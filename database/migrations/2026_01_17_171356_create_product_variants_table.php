<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('size')->nullable(); // যেমন: M, L, XL
            $table->string('color')->nullable(); // যেমন: Red, Blue
            $table->decimal('price', 10, 2); // ভ্যারিয়েন্ট অনুযায়ী দাম আলাদা হতে পারে
            $table->integer('stock_quantity')->default(0); // নির্দিষ্ট এই ভ্যারিয়েন্টের স্টক
            $table->string('barcode')->unique()->nullable(); // POS এর জন্য ইউনিক বারকোড
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
