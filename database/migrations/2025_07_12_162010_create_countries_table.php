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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            
            // Country details
            $table->string('name');
            $table->string('code', 3)->unique();
            
            // Shipping costs
            $table->decimal('min_shipping_cost', 15, 5)->default(0); 
            $table->decimal('max_shipping_cost', 15, 5)->default(0); 
            $table->decimal('avg_shipping_cost', 15, 5)->default(0); 

            // Customs processing times (days)
            $table->integer('min_shipping_time_days')->default(1);
            $table->integer('avg_shipping_time_days')->default(1);
            $table->integer('max_shipping_time_days')->default(1);

            // Tax rates (percentages)
            $table->decimal('customs_duties_rate', 15, 5)->default(0.25); // Custom duties %
            $table->decimal('tva_rate', 15, 5)->default(0.19); // VAT/TVA rate %
            $table->decimal('insurance_rate', 15, 5)->default(0.005); // Insurance % (0.5% typical)
            
            // Freight base rates (per unit)
            $table->decimal('freight_cost', 15, 5)->default(0);
            
            // Port/handling charges (container/shipment)
            $table->decimal('port_handling_fee', 15, 5)->default(20000); // Port handling fee
            
            // System flags
            $table->boolean('allows_imports')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
