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
        Schema::create('loyalty_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('points_earned')->default(0);
            $table->integer('points_redeemed')->default(0);
            $table->integer('balance')->default(0);
            $table->enum('transaction_type', ['earned', 'redeemed']);
            $table->text('description')->nullable();
            $table->date('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'transaction_type']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_points');
    }
};
