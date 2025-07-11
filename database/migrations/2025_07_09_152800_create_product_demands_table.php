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
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('gameweek');

            // Demand fields
            $table->float('min_demand', 13, 3)->default(0);
            $table->float('max_demand', 13, 3)->default(0);
            $table->float('avg_demand', 13, 3)->default(0);

            // Market price
            $table->float('market_price', 13, 3)->default(0);
            
            // Market research fields
            $table->boolean('is_visible')->default(false);
            $table->float('visibility_cost', 13, 3)->default(0);
            $table->float('fluctuation_factor', 13, 3)->default(1.0);
            $table->integer('research_time_days')->default(0);
            
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