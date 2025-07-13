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
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Pricing structure
            $table->decimal('min_sale_price', 10, 3);
            $table->decimal('avg_sale_price', 10, 3);
            $table->decimal('max_sale_price', 10, 3);
            
            // Order and environmental data
            $table->integer('minimum_order_qty')->default(1);
            $table->decimal('carbon_footprint', 8, 3)->default(0); // kg CO2 per unit
            
            $table->timestamps();
            
            // Ensure unique supplier-product combinations
            $table->unique(['supplier_id', 'product_id']);
            
            // Indexes for filtering
            $table->index(['min_sale_price', 'max_sale_price']);
            $table->index('carbon_footprint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_products');
    }
}; 