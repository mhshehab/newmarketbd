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
        Schema::table('orders', function (Blueprint $table) {
            // order_number কলামটি যুক্ত করা হচ্ছে যা ডাটাবেস এররটি সমাধান করবে
            $table->string('order_number')->unique()->after('id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // রোলব্যাক করার সময় কলামটি মুছে ফেলা হবে
            $table->dropColumn('order_number');
        });
    }
};