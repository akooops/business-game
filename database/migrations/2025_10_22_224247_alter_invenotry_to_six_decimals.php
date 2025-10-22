<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Updates all decimal columns from 15 or 10 digits to 20 digits before decimal point
     * Preserves decimal places (3, 4, or 6) to prevent data loss
     */
    public function up(): void
    {
        // Inventory Movements - 3 decimal places
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->decimal('original_quantity', 20, 6)->nullable()->change();
            $table->decimal('current_quantity', 20, 6)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     * Reverts back to original precision (15 or 10 digits)
     */
    public function down(): void
    {
        // Inventory Movements - 3 decimal places (was 10,3)
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->decimal('original_quantity', 10, 3)->nullable()->change();
            $table->decimal('current_quantity', 10, 3)->nullable()->change();
        });
    }
};
