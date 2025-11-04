<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds indexes to frequently queried columns to dramatically
     * improve performance of queue operations, database queries, and AJAX requests.
     * Expected performance improvement: 5-25x faster queries.
     */
    public function up(): void
    {
        // TRANSACTIONS - Most queried table (LeaderboardService, StatsService)
        Schema::table('transactions', function (Blueprint $table) {
            // Composite: company_id + type (filters by both in LeaderboardService)
            $table->index(['company_id', 'type'], 'idx_transactions_company_type');
            // Composite: company_id + transaction_at (time-based queries in StatsService)
            $table->index(['company_id', 'transaction_at'], 'idx_transactions_company_date');
            // Single: transaction_at (for date range queries)
            $table->index('transaction_at', 'idx_transactions_date');
        });

        // SALES
        Schema::table('sales', function (Blueprint $table) {
            // Composite: company_id + status (very common in SalesService)
            $table->index(['company_id', 'status'], 'idx_sales_company_status');
            // Composite: company_id + gameweek
            $table->index(['company_id', 'gameweek'], 'idx_sales_company_gameweek');
            // Composite: product_id + gameweek
            $table->index(['product_id', 'gameweek'], 'idx_sales_product_gameweek');
            // Single: status (for filtering)
            $table->index('status', 'idx_sales_status');
            // Single: initiated_at (time-based queries in StatsService)
            $table->index('initiated_at', 'idx_sales_initiated');
        });

        // PURCHASES
        Schema::table('purchases', function (Blueprint $table) {
            // Composite: company_id + status (very common in ProcurementService)
            $table->index(['company_id', 'status'], 'idx_purchases_company_status');
            // Single: ordered_at (time-based queries in StatsService)
            $table->index('ordered_at', 'idx_purchases_ordered');
        });

        // PRODUCTION_ORDERS
        Schema::table('production_orders', function (Blueprint $table) {
            // Composite: company_machine_id + status (very common in ProductionService)
            $table->index(['company_machine_id', 'status'], 'idx_production_orders_machine_status');
            // Single: status (for filtering)
            $table->index('status', 'idx_production_orders_status');
            // Single: started_at (time-based queries)
            $table->index('started_at', 'idx_production_orders_started');
            // Single: completed_at
            $table->index('completed_at', 'idx_production_orders_completed');
        });

        // EMPLOYEES
        Schema::table('employees', function (Blueprint $table) {
            // Composite: company_id + status (very common in HrService, Events)
            $table->index(['company_id', 'status'], 'idx_employees_company_status');
            // Single: status (for filtering)
            $table->index('status', 'idx_employees_status');
        });

        // COMPANY_MACHINES
        Schema::table('company_machines', function (Blueprint $table) {
            // Composite: company_id + status (very common in MaintenanceService, Events)
            $table->index(['company_id', 'status'], 'idx_company_machines_company_status');
            // Single: status (for filtering)
            $table->index('status', 'idx_company_machines_status');
        });

        // MAINTENANCES
        Schema::table('maintenances', function (Blueprint $table) {
            // Composite: company_machine_id + status
            $table->index(['company_machine_id', 'status'], 'idx_maintenances_machine_status');
            // Single: status
            $table->index('status', 'idx_maintenances_status');
        });

        // LOANS
        Schema::table('loans', function (Blueprint $table) {
            // Composite: company_id + bank_id (for validation in BorrowMoneyRequest)
            $table->index(['company_id', 'bank_id'], 'idx_loans_company_bank');
            // Composite: company_id + paid_at (for filtering unpaid loans)
            $table->index(['company_id', 'paid_at'], 'idx_loans_company_paid');
            // Single: borrowed_at
            $table->index('borrowed_at', 'idx_loans_borrowed');
        });

        // PRODUCT_DEMANDS
        Schema::table('product_demands', function (Blueprint $table) {
            // Composite: product_id + gameweek (very common query in SalesService)
            $table->index(['product_id', 'gameweek'], 'idx_product_demands_product_gameweek');
            // Single: gameweek (for filtering)
            $table->index('gameweek', 'idx_product_demands_gameweek');
        });

        // ADS
        Schema::table('ads', function (Blueprint $table) {
            // Composite: company_id + product_id + status (very common in AdsService)
            $table->index(['company_id', 'product_id', 'status'], 'idx_ads_company_product_status');
            // Single: status
            $table->index('status', 'idx_ads_status');
        });

        // NOTIFICATIONS
        Schema::table('notifications', function (Blueprint $table) {
            // Composite: user_id + read_at (for unread notifications)
            $table->index(['user_id', 'read_at'], 'idx_notifications_user_read');
        });

        // COMPANY_PRODUCTS
        Schema::table('company_products', function (Blueprint $table) {
            // Composite: company_id + product_id (for lookups)
            $table->index(['company_id', 'product_id'], 'idx_company_products_company_product');
        });

        // SUPPLIER_PRODUCTS
        Schema::table('supplier_products', function (Blueprint $table) {
            // Composite: supplier_id + product_id (for lookups)
            $table->index(['supplier_id', 'product_id'], 'idx_supplier_products_supplier_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all indexes in reverse order
        Schema::table('supplier_products', function (Blueprint $table) {
            $table->dropIndex('idx_supplier_products_supplier_product');
        });

        Schema::table('company_products', function (Blueprint $table) {
            $table->dropIndex('idx_company_products_company_product');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_read');
        });

        Schema::table('ads', function (Blueprint $table) {
            $table->dropIndex('idx_ads_status');
            $table->dropIndex('idx_ads_company_product_status');
        });

        Schema::table('product_demands', function (Blueprint $table) {
            $table->dropIndex('idx_product_demands_gameweek');
            $table->dropIndex('idx_product_demands_product_gameweek');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex('idx_loans_borrowed');
            $table->dropIndex('idx_loans_company_paid');
            $table->dropIndex('idx_loans_company_bank');
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropIndex('idx_maintenances_status');
            $table->dropIndex('idx_maintenances_machine_status');
        });

        Schema::table('company_machines', function (Blueprint $table) {
            $table->dropIndex('idx_company_machines_status');
            $table->dropIndex('idx_company_machines_company_status');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('idx_employees_status');
            $table->dropIndex('idx_employees_company_status');
        });

        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropIndex('idx_production_orders_completed');
            $table->dropIndex('idx_production_orders_started');
            $table->dropIndex('idx_production_orders_status');
            $table->dropIndex('idx_production_orders_machine_status');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex('idx_purchases_ordered');
            $table->dropIndex('idx_purchases_company_status');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('idx_sales_initiated');
            $table->dropIndex('idx_sales_status');
            $table->dropIndex('idx_sales_product_gameweek');
            $table->dropIndex('idx_sales_company_gameweek');
            $table->dropIndex('idx_sales_company_status');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_date');
            $table->dropIndex('idx_transactions_company_date');
            $table->dropIndex('idx_transactions_company_type');
        });
    }
};

