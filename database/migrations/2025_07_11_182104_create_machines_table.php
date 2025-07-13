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
            $table->decimal('cost_to_acquire', 15, 5)->default(0);

            // Area required in square meters for the machine
            $table->decimal('area_required', 15, 5)->default(0);
            $table->integer('setup_time_days')->default(1);

            // Performance
            $table->decimal('energy_consumption_hour', 15, 5)->default(0);
            $table->decimal('carbon_emissions_hour', 15, 5)->default(0);
            $table->decimal('quality_factor', 15, 5)->default(0);

            // Speed
            $table->decimal('min_speed_hour', 15, 5)->default(0);
            $table->decimal('avg_speed_hour', 15, 5)->default(0);
            $table->decimal('max_speed_hour', 15, 5)->default(0);

            // Reliability
            $table->decimal('failure_chance_hour', 15, 5)->default(0);
            $table->decimal('reliability_decay_hour', 15, 5)->default(0);
            $table->integer('maintenance_interval_days')->default(1);
            
            // PERT Distribution for Predictive Maintenance
            $table->decimal('min_predictive_maintenance_cost', 15, 5)->default(0);
            $table->decimal('avg_predictive_maintenance_cost', 15, 5)->default(0);
            $table->decimal('max_predictive_maintenance_cost', 15, 5)->default(0);
            $table->integer('min_predictive_maintenance_time_hours')->default(1);
            $table->integer('avg_predictive_maintenance_time_hours')->default(1);
            $table->integer('max_predictive_maintenance_time_hours')->default(1);
            
            // PERT Distribution for Corrective Maintenance
            $table->decimal('min_corrective_maintenance_cost', 15, 5)->default(0);
            $table->decimal('avg_corrective_maintenance_cost', 15, 5)->default(0);
            $table->decimal('max_corrective_maintenance_cost', 15, 5)->default(0);
            $table->integer('min_corrective_maintenance_time_hours')->default(1);
            $table->integer('avg_corrective_maintenance_time_hours')->default(1);
            $table->integer('max_corrective_maintenance_time_hours')->default(1);
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
