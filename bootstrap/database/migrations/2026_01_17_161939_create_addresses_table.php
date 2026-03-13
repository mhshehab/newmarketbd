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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('address_line');
            $table->string('city');
            $table->string('area')->nullable();
            $table->string('lat')->nullable(); // ডেলিভারি অ্যাপের ম্যাপের জন্য
            $table->string('long')->nullable(); // ডেলিভারি অ্যাপের ম্যাপের জন্য
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
