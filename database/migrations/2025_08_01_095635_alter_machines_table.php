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
            // Loss on sale
            $table->decimal('loss_on_sale_days', 15, 3)->default(0)->after('cost_to_acquire');
        });

        Schema::table('company_machines', function (Blueprint $table) {
            $table->decimal('loss_on_sale_days', 15, 3)->default(0)->after('reliability_decay_days');
            $table->decimal('acquisition_cost', 15, 3)->default(0)->after('loss_on_sale_days');
            $table->decimal('current_value', 15, 3)->default(0)->after('acquisition_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropColumn('loss_on_sale_days');
        });

        Schema::table('company_machines', function (Blueprint $table) {
            $table->dropColumn('loss_on_sale_days');
            $table->dropColumn('acquisition_cost');
            $table->dropColumn('current_value');
        });
    }
};
