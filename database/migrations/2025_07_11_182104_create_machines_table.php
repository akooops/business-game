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
            $table->float('cost_to_acquire', 13, 3);

            // Performance
            $table->float('hourly_energy_consumption', 13, 3);
            $table->float('hourly_carbon_emissions', 13, 3);
            $table->float('hourly_quality_factor', 13, 3);

            // Speed
            $table->float('hourly_speed_min', 13, 3);
            $table->float('hourly_speed_avg', 13, 3);
            $table->float('hourly_speed_max', 13, 3);

            // Reliability
            $table->float('hourly_failure_chance', 13, 5);
            $table->float('hourly_reliability_decay', 13, 5);
            $table->integer('maintenance_interval_days');
            
            // PERT Distribution for Predictive Maintenance
            $table->float('predictive_maintenance_cost_min', 13, 3);
            $table->float('predictive_maintenance_cost_avg', 13, 3);
            $table->float('predictive_maintenance_cost_max', 13, 3);
            $table->integer('predictive_maintenance_delay_min_hours');
            $table->integer('predictive_maintenance_delay_avg_hours');
            $table->integer('predictive_maintenance_delay_max_hours');
            
            // PERT Distribution for Corrective Maintenance
            $table->float('corrective_maintenance_cost_min', 13, 3);
            $table->float('corrective_maintenance_cost_avg', 13, 3);
            $table->float('corrective_maintenance_cost_max', 13, 3);
            $table->integer('corrective_maintenance_delay_min_hours');
            $table->integer('corrective_maintenance_delay_avg_hours');
            $table->integer('corrective_maintenance_delay_max_hours');
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
