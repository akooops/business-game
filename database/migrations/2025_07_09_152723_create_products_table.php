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
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['raw_material', 'component', 'finished_product']);

            $table->float('elasticity_coefficient', 13, 3)->default(1.000);

            $table->integer('shelf_life_days')->nullable();
            $table->boolean('has_expiration')->default(false);

            $table->string('measurement_unit')->default('unit');

            $table->boolean('requires_rd')->default(false);
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
