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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('payment_method'); // যেমন: bKash, Nagad, Cash, Card
            $table->string('transaction_id')->nullable(); // অনলাইন পেমেন্টের ক্ষেত্রে
            $table->decimal('amount', 10, 2); // কত টাকা পেমেন্ট করা হয়েছে
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
