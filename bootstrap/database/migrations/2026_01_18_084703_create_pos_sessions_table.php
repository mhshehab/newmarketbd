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
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            // কোন স্টাফ সেশনটি চালাচ্ছে (users টেবিল থেকে আসবে)
            $table->foreignId('staff_id')->constrained('users')->onDelete('cascade');

            $table->decimal('opening_balance', 12, 2)->default(0); // দোকান খোলার সময় ক্যাশে থাকা টাকা
            $table->decimal('closing_balance', 12, 2)->nullable(); // দোকান বন্ধের সময় ক্যাশে থাকা টাকা

            $table->string('status')->default('open'); // open, closed
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_sessions');
    }
};
