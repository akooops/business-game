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
        Schema::table('product_recipes', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });

        Schema::table('product_recipes', function (Blueprint $table) {
            $table->decimal('quantity', 15, 4)->default(0)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_recipes', function (Blueprint $table) {
            $table->decimal('quantity', 15, 3)->default(0)->after('id');
        });
    }
};
