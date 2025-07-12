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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Product details
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['raw_material', 'component', 'finished_product']);

            // Elasticity coefficient for demand adjustments
            $table->decimal('elasticity_coefficient', 15, 5)->default(1);

            // Shelf life and expiration
            $table->integer('shelf_life_days')->nullable();
            $table->boolean('has_expiration')->default(false);

            // Measurement unit
            $table->string('measurement_unit')->default('unit');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
