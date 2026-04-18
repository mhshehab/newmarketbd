<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * রান করুন (Migration Run).
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // বারকোড কলাম ইতিমধ্যে আছে, তাই এখানে আর যোগ করা হবে না
            // $table->string('barcode')->unique()->nullable()->after('name');

            // লো-স্টক অ্যালার্ট থ্রেশহোল্ড (ডিফল্ট ১০ সেট করা হয়েছে)
            $table->integer('low_stock_threshold')->default(10)->after('stock_quantity');
            
            // পণ্যের মেয়াদের তারিখ (Expiry date)
            $table->date('expiry_date')->nullable()->after('low_stock_threshold');
        });
    }

    /**
     * রিভার্স করুন (Migration Rollback).
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // একসাথে তিনটি কলাম ড্রপ করা হচ্ছে
            $table->dropColumn(['barcode', 'low_stock_threshold', 'expiry_date']);
        });
    }
};