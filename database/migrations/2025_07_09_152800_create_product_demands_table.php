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
        Schema::create('product_demands', function (Blueprint $table) {
            $table->id();

            // Gameweek
            $table->integer('gameweek');

            // Demand fields
            $table->decimal('min_demand', 15, 3)->default(0);
            $table->decimal('max_demand', 15, 3)->default(0);
            $table->decimal('real_demand', 15, 3)->default(0);

            // Market price
            $table->decimal('market_price', 15, 3)->default(0);
            
            // Foreign keys
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_demands');
    }
}; 