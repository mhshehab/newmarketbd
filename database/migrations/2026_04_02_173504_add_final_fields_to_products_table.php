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
        Schema::table('products', function (Blueprint $table) {
            // যদি status কলামটি না থাকে তবেই তৈরি করবে
            if (!Schema::hasColumn('products', 'status')) {
                $table->boolean('status')->default(true)->after('image');
            }
            
            // যদি is_featured কলামটি না থাকে তবেই তৈরি করবে
            if (!Schema::hasColumn('products', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // রোলব্যাক করার সময় কলামগুলো মুছে ফেলার কোড
            $table->dropColumn(['status', 'is_featured']);
        });
    }
};