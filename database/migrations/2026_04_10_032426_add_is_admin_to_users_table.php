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
        Schema::table('users', function (Blueprint $table) {
            // ইমেইল কলামের পরে is_admin কলামটি যুক্ত করা হচ্ছে
            $table->boolean('is_admin')->default(false)->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // মাইগ্রেশন রোলব্যাক করলে কলামটি রিমুভ হয়ে যাবে
            $table->dropColumn('is_admin');
        });
    }
};