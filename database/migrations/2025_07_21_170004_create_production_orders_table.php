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
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();

            $table->decimal('quantity', 10, 3);
            $table->integer('time_to_complete');
            $table->decimal('quality_factor', 10, 3);
            $table->decimal('employee_efficiency_factor', 10, 3);
            $table->decimal('carbon_footprint', 10, 3);

            $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->foreignId('company_machine_id')->constrained('company_machines');
            $table->foreignId('product_id')->constrained('products');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
