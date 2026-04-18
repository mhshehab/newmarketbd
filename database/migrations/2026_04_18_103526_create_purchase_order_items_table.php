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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 10, 2);
            $table->decimal('quantity_received', 10, 2)->default(0);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 12, 2);
            $table->date('expiry_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['purchase_order_id', 'product_id']);
            $table->index(['expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
