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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->enum('type', [
                'technology',
                'purchase',
                'inventory',
                'sale_shipping',
                'sale_payment',
                'employee_recruitment',
                'employee_salary',
                'machine_setup',
                'machine_sold',
                'machine_operations',
                'maintenance',
                'marketing',
                'loan_received',
                'loan_payment',
            ]);

            $table->decimal('amount', 10, 3);
            $table->timestamp('transaction_at')->nullable();

            $table->foreignId('company_id')->constrained('companies');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
