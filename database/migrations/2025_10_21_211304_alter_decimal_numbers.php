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
        // Company Machines - 3 decimal places
        Schema::table('company_machines', function (Blueprint $table) {
            $table->decimal('speed', 20, 3)->default(0)->change();
            $table->decimal('quality_factor', 20, 3)->default(0)->change();
            $table->decimal('operations_cost', 20, 3)->default(0)->change();
            $table->decimal('reliability_decay_days', 20, 3)->default(0)->change();
            $table->decimal('maintenance_cost', 20, 3)->default(0)->change();
            $table->decimal('current_reliability', 20, 3)->default(0)->change();
            $table->decimal('loss_on_sale_days', 20, 3)->default(0)->change();
            $table->decimal('acquisition_cost', 20, 3)->default(0)->change();
            $table->decimal('current_value', 20, 3)->default(0)->change();
        });

        // Company Machines - 4 decimal places (carbon_footprint)
        Schema::table('company_machines', function (Blueprint $table) {
            $table->decimal('carbon_footprint', 20, 4)->default(0)->change();
        });

        // Production Orders - 3 decimal places
        Schema::table('production_orders', function (Blueprint $table) {
            $table->decimal('quantity', 20, 3)->change();
            $table->decimal('quality_factor', 20, 3)->change();
            $table->decimal('employee_efficiency_factor', 20, 3)->change();
            $table->decimal('carbon_footprint', 20, 3)->change();
        });

        // Maintenances - 3 decimal places
        Schema::table('maintenances', function (Blueprint $table) {
            $table->decimal('maintenances_cost', 20, 3)->change();
        });

        // Employees - 3 decimal places
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('salary_month', 20, 3)->default(0)->change();
            $table->decimal('recruitment_cost', 20, 3)->default(0)->change();
            $table->decimal('current_mood', 20, 3)->default(0)->change();
            $table->decimal('mood_decay_rate_days', 20, 3)->default(0)->change();
            $table->decimal('efficiency_factor', 20, 3)->default(0)->change();
        });

        // Sales - 3 decimal places
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('quantity', 20, 3)->default(0)->change();
            $table->decimal('sale_price', 20, 3)->default(0)->change();
            $table->decimal('shipping_cost', 20, 3)->default(0)->change();
        });

        // Purchases - 3 decimal places
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('quantity', 20, 3)->default(0)->change();
            $table->decimal('sale_price', 20, 3)->default(0)->change();
            $table->decimal('shipping_cost', 20, 3)->default(0)->change();
            $table->decimal('customs_duties', 20, 3)->default(0)->change();
            $table->decimal('total_cost', 20, 3)->default(0)->change();
            $table->decimal('carbon_footprint', 20, 3)->default(0)->change();
        });

        // Company Technologies - 3 decimal places
        Schema::table('company_technologies', function (Blueprint $table) {
            $table->decimal('research_cost', 20, 3)->default(0)->change();
        });

        // Loans - 3 decimal places
        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('amount', 20, 3)->change();
            $table->decimal('interest_rate', 20, 3)->change();
            $table->decimal('total_amount', 20, 3)->change();
            $table->decimal('monthly_payment', 20, 3)->change();
            $table->decimal('remaining_amount', 20, 3)->change();
        });

        // Ads - 3 decimal places
        Schema::table('ads', function (Blueprint $table) {
            $table->decimal('price', 20, 3)->default(0)->change();
            $table->decimal('market_impact_percentage', 20, 3)->default(0)->change();
        });

        // Transactions - 3 decimal places
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('amount', 20, 3)->change();
        });

        // Inventory Movements - 3 decimal places
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->decimal('original_quantity', 20, 3)->nullable()->change();
            $table->decimal('current_quantity', 20, 3)->nullable()->change();
        });

        // Product Recipes - 6 decimal places (special case for precision)
        Schema::table('product_recipes', function (Blueprint $table) {
            $table->decimal('quantity', 20, 6)->default(0)->change();
        });

        // Products - 3 decimal places
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('avg_demand', 20, 3)->default(0)->change();
            $table->decimal('avg_market_price', 20, 3)->default(0)->change();
            $table->decimal('storage_cost', 20, 3)->default(0)->change();
            $table->decimal('elasticity_coefficient', 20, 3)->default(1)->change();
        });

        // Machines - 3 decimal places
        Schema::table('machines', function (Blueprint $table) {
            $table->decimal('cost_to_acquire', 20, 3)->default(0)->change();
            $table->decimal('operations_cost', 20, 3)->default(0)->change();
            $table->decimal('quality_factor', 20, 3)->default(0)->change();
            $table->decimal('min_speed', 20, 3)->default(0)->change();
            $table->decimal('max_speed', 20, 3)->default(0)->change();
            $table->decimal('reliability_decay_days', 20, 3)->default(0)->change();
            $table->decimal('min_maintenance_cost', 20, 3)->default(0)->change();
            $table->decimal('max_maintenance_cost', 20, 3)->default(0)->change();
            $table->decimal('loss_on_sale_days', 20, 3)->default(0)->change();
        });

        // Machines - 4 decimal places (carbon_footprint)
        Schema::table('machines', function (Blueprint $table) {
            $table->decimal('carbon_footprint', 20, 4)->default(0)->change();
        });

        // Advertisers - 3 decimal places
        Schema::table('advertisers', function (Blueprint $table) {
            $table->decimal('min_price', 20, 3)->default(0)->change();
            $table->decimal('max_price', 20, 3)->default(0)->change();
            $table->decimal('real_price', 20, 3)->default(0)->change();
            $table->decimal('min_market_impact_percentage', 20, 3)->default(0)->change();
            $table->decimal('max_market_impact_percentage', 20, 3)->default(0)->change();
        });

        // Companies - 3 decimal places
        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('funds', 20, 3)->default(0)->change();
            $table->decimal('unpaid_loans', 20, 3)->default(0)->change();
            $table->decimal('carbon_footprint', 20, 3)->default(0)->change();
        });

        // Banks - 3 decimal places
        Schema::table('banks', function (Blueprint $table) {
            $table->decimal('loan_interest_rate', 20, 3)->default(0)->change();
            $table->decimal('loan_max_amount', 20, 3)->default(0)->change();
        });

        // Company Products - 3 decimal places
        Schema::table('company_products', function (Blueprint $table) {
            $table->decimal('available_stock', 20, 3)->default(0)->change();
            $table->decimal('sale_price', 20, 3)->default(0)->change();
        });

        // Supplier Products - 3 decimal places
        Schema::table('supplier_products', function (Blueprint $table) {
            $table->decimal('min_sale_price', 20, 3)->change();
            $table->decimal('max_sale_price', 20, 3)->change();
            $table->decimal('real_sale_price', 20, 3)->change();
        });

        // Suppliers - 3 decimal places
        Schema::table('suppliers', function (Blueprint $table) {
            $table->decimal('min_shipping_cost', 20, 3)->default(0)->change();
            $table->decimal('max_shipping_cost', 20, 3)->default(0)->change();
            $table->decimal('real_shipping_cost', 20, 3)->default(0)->change();
            $table->decimal('carbon_footprint', 20, 3)->default(0)->change();
        });

        // Employee Profiles - 3 decimal places
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->decimal('min_salary_month', 20, 3)->default(0)->change();
            $table->decimal('max_salary_month', 20, 3)->default(0)->change();
            $table->decimal('min_recruitment_cost', 20, 3)->default(0)->change();
            $table->decimal('max_recruitment_cost', 20, 3)->default(0)->change();
        });

        // Wilayas - 3 decimal places
        Schema::table('wilayas', function (Blueprint $table) {
            $table->decimal('min_shipping_cost', 20, 3)->default(0)->change();
            $table->decimal('max_shipping_cost', 20, 3)->default(0)->change();
            $table->decimal('real_shipping_cost', 20, 3)->default(0)->change();
        });

        // Countries - 3 decimal places
        Schema::table('countries', function (Blueprint $table) {
            $table->decimal('customs_duties_rate', 20, 3)->default(0.25)->change();
        });

        // Product Demands - 3 decimal places
        Schema::table('product_demands', function (Blueprint $table) {
            $table->decimal('min_demand', 20, 3)->default(0)->change();
            $table->decimal('max_demand', 20, 3)->default(0)->change();
            $table->decimal('real_demand', 20, 3)->default(0)->change();
            $table->decimal('market_price', 20, 3)->default(0)->change();
        });

        // Technologies - 3 decimal places
        Schema::table('technologies', function (Blueprint $table) {
            $table->decimal('research_cost', 20, 3)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     * Reverts back to original precision (15 or 10 digits)
     */
    public function down(): void
    {
        // Company Machines - 3 decimal places
        Schema::table('company_machines', function (Blueprint $table) {
            $table->decimal('speed', 15, 3)->default(0)->change();
            $table->decimal('quality_factor', 15, 3)->default(0)->change();
            $table->decimal('operations_cost', 15, 3)->default(0)->change();
            $table->decimal('reliability_decay_days', 15, 3)->default(0)->change();
            $table->decimal('maintenance_cost', 15, 3)->default(0)->change();
            $table->decimal('current_reliability', 15, 3)->default(0)->change();
            $table->decimal('loss_on_sale_days', 15, 3)->default(0)->change();
            $table->decimal('acquisition_cost', 15, 3)->default(0)->change();
            $table->decimal('current_value', 15, 3)->default(0)->change();
        });

        // Company Machines - 4 decimal places
        Schema::table('company_machines', function (Blueprint $table) {
            $table->decimal('carbon_footprint', 15, 4)->default(0)->change();
        });

        // Production Orders - 3 decimal places (was 10,3)
        Schema::table('production_orders', function (Blueprint $table) {
            $table->decimal('quantity', 10, 3)->change();
            $table->decimal('quality_factor', 10, 3)->change();
            $table->decimal('employee_efficiency_factor', 10, 3)->change();
            $table->decimal('carbon_footprint', 10, 3)->change();
        });

        // Maintenances - 3 decimal places (was 10,3)
        Schema::table('maintenances', function (Blueprint $table) {
            $table->decimal('maintenances_cost', 10, 3)->change();
        });

        // Employees - 3 decimal places
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('salary_month', 15, 3)->default(0)->change();
            $table->decimal('recruitment_cost', 15, 3)->default(0)->change();
            $table->decimal('current_mood', 15, 3)->default(0)->change();
            $table->decimal('mood_decay_rate_days', 15, 3)->default(0)->change();
            $table->decimal('efficiency_factor', 15, 3)->default(0)->change();
        });

        // Sales - 3 decimal places
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('quantity', 15, 3)->default(0)->change();
            $table->decimal('sale_price', 15, 3)->default(0)->change();
            $table->decimal('shipping_cost', 15, 3)->default(0)->change();
        });

        // Purchases - 3 decimal places
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('quantity', 15, 3)->default(0)->change();
            $table->decimal('sale_price', 15, 3)->default(0)->change();
            $table->decimal('shipping_cost', 15, 3)->default(0)->change();
            $table->decimal('customs_duties', 15, 3)->default(0)->change();
            $table->decimal('total_cost', 15, 3)->default(0)->change();
            $table->decimal('carbon_footprint', 15, 3)->default(0)->change();
        });

        // Company Technologies - 3 decimal places
        Schema::table('company_technologies', function (Blueprint $table) {
            $table->decimal('research_cost', 15, 3)->default(0)->change();
        });

        // Loans - 3 decimal places
        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('amount', 15, 3)->change();
            $table->decimal('interest_rate', 15, 3)->change();
            $table->decimal('total_amount', 15, 3)->change();
            $table->decimal('monthly_payment', 15, 3)->change();
            $table->decimal('remaining_amount', 15, 3)->change();
        });

        // Ads - 3 decimal places
        Schema::table('ads', function (Blueprint $table) {
            $table->decimal('price', 15, 3)->default(0)->change();
            $table->decimal('market_impact_percentage', 15, 3)->default(0)->change();
        });

        // Transactions - 3 decimal places (was 10,3)
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('amount', 10, 3)->change();
        });

        // Inventory Movements - 3 decimal places (was 10,3)
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->decimal('original_quantity', 10, 3)->nullable()->change();
            $table->decimal('current_quantity', 10, 3)->nullable()->change();
        });

        // Product Recipes - 6 decimal places
        Schema::table('product_recipes', function (Blueprint $table) {
            $table->decimal('quantity', 15, 6)->default(0)->change();
        });

        // Products - 3 decimal places
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('avg_demand', 15, 3)->default(0)->change();
            $table->decimal('avg_market_price', 15, 3)->default(0)->change();
            $table->decimal('storage_cost', 15, 3)->default(0)->change();
            $table->decimal('elasticity_coefficient', 15, 3)->default(1)->change();
        });

        // Machines - 3 decimal places
        Schema::table('machines', function (Blueprint $table) {
            $table->decimal('cost_to_acquire', 15, 3)->default(0)->change();
            $table->decimal('operations_cost', 15, 3)->default(0)->change();
            $table->decimal('quality_factor', 15, 3)->default(0)->change();
            $table->decimal('min_speed', 15, 3)->default(0)->change();
            $table->decimal('max_speed', 15, 3)->default(0)->change();
            $table->decimal('reliability_decay_days', 15, 3)->default(0)->change();
            $table->decimal('min_maintenance_cost', 15, 3)->default(0)->change();
            $table->decimal('max_maintenance_cost', 15, 3)->default(0)->change();
            $table->decimal('loss_on_sale_days', 15, 3)->default(0)->change();
        });

        // Machines - 4 decimal places
        Schema::table('machines', function (Blueprint $table) {
            $table->decimal('carbon_footprint', 15, 4)->default(0)->change();
        });

        // Advertisers - 3 decimal places
        Schema::table('advertisers', function (Blueprint $table) {
            $table->decimal('min_price', 15, 3)->default(0)->change();
            $table->decimal('max_price', 15, 3)->default(0)->change();
            $table->decimal('real_price', 15, 3)->default(0)->change();
            $table->decimal('min_market_impact_percentage', 15, 3)->default(0)->change();
            $table->decimal('max_market_impact_percentage', 15, 3)->default(0)->change();
        });

        // Companies - 3 decimal places
        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('funds', 15, 3)->default(0)->change();
            $table->decimal('unpaid_loans', 15, 3)->default(0)->change();
            $table->decimal('carbon_footprint', 15, 3)->default(0)->change();
        });

        // Banks - 3 decimal places
        Schema::table('banks', function (Blueprint $table) {
            $table->decimal('loan_interest_rate', 15, 3)->default(0)->change();
            $table->decimal('loan_max_amount', 15, 3)->default(0)->change();
        });

        // Company Products - 3 decimal places
        Schema::table('company_products', function (Blueprint $table) {
            $table->decimal('available_stock', 15, 3)->default(0)->change();
            $table->decimal('sale_price', 15, 3)->default(0)->change();
        });

        // Supplier Products - 3 decimal places
        Schema::table('supplier_products', function (Blueprint $table) {
            $table->decimal('min_sale_price', 15, 3)->change();
            $table->decimal('max_sale_price', 15, 3)->change();
            $table->decimal('real_sale_price', 15, 3)->change();
        });

        // Suppliers - 3 decimal places
        Schema::table('suppliers', function (Blueprint $table) {
            $table->decimal('min_shipping_cost', 15, 3)->default(0)->change();
            $table->decimal('max_shipping_cost', 15, 3)->default(0)->change();
            $table->decimal('real_shipping_cost', 15, 3)->default(0)->change();
            $table->decimal('carbon_footprint', 15, 3)->default(0)->change();
        });

        // Employee Profiles - 3 decimal places
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->decimal('min_salary_month', 15, 3)->default(0)->change();
            $table->decimal('max_salary_month', 15, 3)->default(0)->change();
            $table->decimal('min_recruitment_cost', 15, 3)->default(0)->change();
            $table->decimal('max_recruitment_cost', 15, 3)->default(0)->change();
        });

        // Wilayas - 3 decimal places
        Schema::table('wilayas', function (Blueprint $table) {
            $table->decimal('min_shipping_cost', 15, 3)->default(0)->change();
            $table->decimal('max_shipping_cost', 15, 3)->default(0)->change();
            $table->decimal('real_shipping_cost', 15, 3)->default(0)->change();
        });

        // Countries - 3 decimal places
        Schema::table('countries', function (Blueprint $table) {
            $table->decimal('customs_duties_rate', 15, 3)->default(0.25)->change();
        });

        // Product Demands - 3 decimal places
        Schema::table('product_demands', function (Blueprint $table) {
            $table->decimal('min_demand', 15, 3)->default(0)->change();
            $table->decimal('max_demand', 15, 3)->default(0)->change();
            $table->decimal('real_demand', 15, 3)->default(0)->change();
            $table->decimal('market_price', 15, 3)->default(0)->change();
        });

        // Technologies - 3 decimal places
        Schema::table('technologies', function (Blueprint $table) {
            $table->decimal('research_cost', 15, 3)->default(0)->change();
        });
    }
};
