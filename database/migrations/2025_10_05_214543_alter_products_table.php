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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('avg_demand', 15, 3)->default(0)->after('elasticity_coefficient');
            $table->decimal('avg_market_price', 15, 3)->default(0)->after('avg_demand');
            $table->boolean('is_saleable')->default(true)->after('avg_demand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('avg_demand');
            $table->dropColumn('avg_market_price');
            $table->dropColumn('is_saleable');
        });
    }
};
