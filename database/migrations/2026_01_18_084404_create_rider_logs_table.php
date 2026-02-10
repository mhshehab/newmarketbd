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
        Schema::create('rider_logs', function (Blueprint $table) {
            $table->id();
            // ডেলিভারি ম্যানের সাথে কানেক্ট করা
            $table->foreignId('delivery_boy_id')->constrained('users')->onDelete('cascade');

            // লোকেশন ডাটা (Latitude এবং Longitude)
            $table->decimal('current_lat', 10, 8);
            $table->decimal('current_long', 11, 8);
        
        // কখন এই লোকেশনে ছিল তার রেকর্ড
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rider_logs');
    }
};
