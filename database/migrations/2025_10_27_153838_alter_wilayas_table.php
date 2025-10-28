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
        Schema::table('wilayas', function (Blueprint $table) {
            $table->dropColumn('min_shipping_cost');
            $table->dropColumn('max_shipping_cost');
            $table->dropColumn('real_shipping_cost');
            $table->dropColumn('min_shipping_time_days');
            $table->dropColumn('max_shipping_time_days');
            $table->dropColumn('real_shipping_time_days');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('shipping_cost');
            $table->dropColumn('shipping_time_days');
            $table->dropColumn('confirmed_at');
            $table->enum('status', ['initiated', 'delivered', 'cancelled'])->default('initiated')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wilayas', function (Blueprint $table) {
            $table->decimal('min_shipping_cost', 15, 3)->default(0)->after('name');
            $table->decimal('max_shipping_cost', 15, 3)->default(0)->after('min_shipping_cost');
            $table->decimal('real_shipping_cost', 15, 3)->default(0)->after('max_shipping_cost');
            $table->integer('min_shipping_time_days')->default(1)->after('real_shipping_cost');
            $table->integer('max_shipping_time_days')->default(1)->after('min_shipping_time_days');
            $table->integer('real_shipping_time_days')->default(1)->after('max_shipping_time_days');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('shipping_cost', 15, 3)->default(0)->after('sale_price');
            $table->integer('shipping_time_days')->default(1)->after('shipping_cost');
            $table->timestamp('confirmed_at')->nullable()->after('shipping_time_days');
            $table->enum('status', ['initiated', 'confirmed', 'delivered', 'cancelled'])->default('initiated')->change();
        });
    }
};
