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
        Schema::table('machines', function (Blueprint $table) {
            $table->text('description')->nullable()->after('manufacturer');
            $table->dropColumn('carbon_footprint');
        });

        Schema::table('machines', function (Blueprint $table) {
            $table->decimal('carbon_footprint', 15, 4)->default(0)->after('operations_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->decimal('carbon_footprint', 15, 3)->default(0)->after('operations_cost');
        });
    }
};
