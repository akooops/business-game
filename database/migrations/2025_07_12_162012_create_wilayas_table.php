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
        Schema::create('wilayas', function (Blueprint $table) {
            $table->id();

            // Wilaya details
            $table->string('name');

            // Shipping to costs from Algiers to this wilaya
            $table->decimal('min_shipping_cost', 15, 3)->default(0); 
            $table->decimal('max_shipping_cost', 15, 3)->default(0); 

            // Shipping to times (days) from Algiers to this wilaya
            $table->integer('min_shipping_time_days')->default(1);
            $table->integer('max_shipping_time_days')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wilayas');
    }
}; 