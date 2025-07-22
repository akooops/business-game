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
        Schema::create('machines', function (Blueprint $table) {
            $table->id();

            // Machine details
            $table->string('name');
            $table->string('model');
            $table->string('manufacturer');
            $table->decimal('cost_to_acquire', 15, 3)->default(0);

            // Performance
            $table->decimal('operation_cost', 15 ,3)->default(0);

            $table->decimal('carbon_footprint', 15, 3)->default(0);
            $table->decimal('quality_factor', 15, 3)->default(0);

            // Speed
            $table->decimal('min_speed', 15, 3)->default(0);
            $table->decimal('avg_speed', 15, 3)->default(0);
            $table->decimal('max_speed', 15, 3)->default(0);

            // Reliability
            $table->decimal('reliability_decay_days', 15, 3)->default(0);
            $table->integer('maintenance_interval_days')->default(1);
            
            // PERT Distribution for Predictive Maintenance
            $table->decimal('min_maintenance_cost', 15, 3)->default(0);
            $table->decimal('avg_maintenance_cost', 15, 3)->default(0);
            $table->decimal('max_maintenance_cost', 15, 3)->default(0);
            $table->integer('min_maintenance_time_days')->default(1);
            $table->integer('avg_maintenance_time_days')->default(1);
            $table->integer('max_maintenance_time_days')->default(1);

            // Foreign keys
            $table->foreignId('employee_profile_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
