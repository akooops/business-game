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
        // Company Machines
        Schema::table('company_machines', function (Blueprint $table) {
            $table->dateTime('setup_at')->nullable()->change();
            $table->dateTime('last_maintenance_at')->nullable()->change();
            $table->dateTime('last_broken_at')->nullable()->change();
        });

        // Production Orders
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dateTime('started_at')->nullable()->change();
            $table->dateTime('completed_at')->nullable()->change();
        });

        // Maintenances
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dateTime('started_at')->nullable()->change();
            $table->dateTime('completed_at')->nullable()->change();
        });

        // Employees
        Schema::table('employees', function (Blueprint $table) {
            $table->dateTime('applied_at')->nullable()->change();
            $table->dateTime('hired_at')->nullable()->change();
            $table->dateTime('fired_at')->nullable()->change();
            $table->dateTime('resigned_at')->nullable()->change();
            $table->dateTime('last_promotion_at')->nullable()->change();
        });

        // Sales
        Schema::table('sales', function (Blueprint $table) {
            $table->dateTime('initiated_at')->nullable()->change();
            $table->dateTime('confirmed_at')->nullable()->change();
            $table->dateTime('delivered_at')->nullable()->change();
        });

        // Purchases
        Schema::table('purchases', function (Blueprint $table) {
            $table->dateTime('ordered_at')->nullable()->change();
            $table->dateTime('delivered_at')->nullable()->change();
        });

        // Inventory Movements
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dateTime('moved_at')->nullable()->change();
        });

        // Transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->dateTime('transaction_at')->nullable()->change();
        });

        // Ads
        Schema::table('ads', function (Blueprint $table) {
            $table->dateTime('started_at')->nullable()->change();
            $table->dateTime('completed_at')->nullable()->change();
        });

        // Loans
        Schema::table('loans', function (Blueprint $table) {
            $table->dateTime('borrowed_at')->nullable()->change();
            $table->dateTime('paid_at')->nullable()->change();
        });

        // Company Technologies
        Schema::table('company_technologies', function (Blueprint $table) {
            $table->dateTime('started_at')->nullable()->change();
            $table->dateTime('completed_at')->nullable()->change();
        });

        // Notifications
        Schema::table('notifications', function (Blueprint $table) {
            $table->dateTime('read_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Company Machines
        Schema::table('company_machines', function (Blueprint $table) {
            $table->timestamp('setup_at')->nullable()->change();
            $table->timestamp('last_maintenance_at')->nullable()->change();
            $table->timestamp('last_broken_at')->nullable()->change();
        });

        // Production Orders
        Schema::table('production_orders', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->change();
            $table->timestamp('completed_at')->nullable()->change();
        });

        // Maintenances
        Schema::table('maintenances', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->change();
            $table->timestamp('completed_at')->nullable()->change();
        });

        // Employees
        Schema::table('employees', function (Blueprint $table) {
            $table->timestamp('applied_at')->nullable()->change();
            $table->timestamp('hired_at')->nullable()->change();
            $table->timestamp('fired_at')->nullable()->change();
            $table->timestamp('resigned_at')->nullable()->change();
            $table->timestamp('last_promotion_at')->nullable()->change();
        });

        // Sales
        Schema::table('sales', function (Blueprint $table) {
            $table->timestamp('initiated_at')->nullable()->change();
            $table->timestamp('confirmed_at')->nullable()->change();
            $table->timestamp('delivered_at')->nullable()->change();
        });

        // Purchases
        Schema::table('purchases', function (Blueprint $table) {
            $table->timestamp('ordered_at')->nullable()->change();
            $table->timestamp('delivered_at')->nullable()->change();
        });

        // Inventory Movements
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->timestamp('moved_at')->nullable()->change();
        });

        // Transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->timestamp('transaction_at')->nullable()->change();
        });

        // Ads
        Schema::table('ads', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->change();
            $table->timestamp('completed_at')->nullable()->change();
        });

        // Loans
        Schema::table('loans', function (Blueprint $table) {
            $table->timestamp('borrowed_at')->nullable()->change();
            $table->timestamp('paid_at')->nullable()->change();
        });

        // Company Technologies
        Schema::table('company_technologies', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->change();
            $table->timestamp('completed_at')->nullable()->change();
        });

        // Notifications
        Schema::table('notifications', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->change();
        });
    }
};
