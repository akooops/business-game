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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_international')->default(false);

            // Research costs
            $table->boolean('needs_research')->default(false);
            $table->decimal('research_cost', 10, 5)->default(0);
            $table->integer('research_time_days')->default(1);

            // Shipping costs
            $table->decimal('min_shipping_cost', 15, 5)->default(0); 
            $table->decimal('max_shipping_cost', 15, 5)->default(0); 
            $table->decimal('avg_shipping_cost', 15, 5)->default(0); 
            $table->decimal('real_shipping_cost', 15, 5)->default(0); 

            // Shipping times (days)
            $table->integer('min_shipping_time_days')->default(1);
            $table->integer('avg_shipping_time_days')->default(1);
            $table->integer('max_shipping_time_days')->default(1);

            //Carbon footprint per unit
            $table->decimal('carbon_footprint', 15, 5)->default(0);

            // Location relationships - one or the other based on type
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('wilaya_id')->nullable()->constrained()->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
}; 