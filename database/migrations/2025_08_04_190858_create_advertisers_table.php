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
        Schema::create('advertisers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('min_price', 15, 3)->default(0);
            $table->decimal('max_price', 15, 3)->default(0);
            $table->decimal('real_price', 15, 3)->default(0);

            $table->decimal('min_market_impact_percentage', 15, 3)->default(0);
            $table->decimal('max_market_impact_percentage', 15, 3)->default(0);
            $table->integer('duration_days')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisers');
    }
};
