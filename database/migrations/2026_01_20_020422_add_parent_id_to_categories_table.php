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
            // parent_id কলাম যোগ করা হচ্ছে যা self-referencing foreign key
            if (!Schema::hasColumn('categories', 'parent_id')) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('slug')
                    ->constrained('categories')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // প্রথমে foreign key ড্রপ করতে হয়, তারপর কলাম
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
