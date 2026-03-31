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
        Schema::table('categories', function (Blueprint $table) {
            // এইখানে আইকন কলামটি যোগ করুন
            $table->string('icon')->nullable()->after('parent_id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // মাইগ্রেশন রোলব্যাক করলে কলামটি মুছে যাবে
            $table->dropColumn('icon');
        });
    }
};