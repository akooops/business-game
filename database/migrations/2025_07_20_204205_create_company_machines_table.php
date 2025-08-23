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
        Schema::create('company_machines', function (Blueprint $table) {
            $table->id();

            $table->decimal('speed', 15, 3)->default(0);
            $table->decimal('quality_factor', 15, 3)->default(0);
            $table->decimal('operations_cost', 15, 3)->default(0);
            $table->decimal('carbon_footprint', 15, 3)->default(0);
            $table->decimal('reliability_decay_days', 15, 3)->default(0);
            $table->decimal('maintenance_cost', 15, 3)->default(0);
            $table->integer('maintenance_time_days')->default(0);

            $table->decimal('current_reliability', 15, 3)->default(0);
            $table->enum('status', ['active', 'inactive', 'maintenance', 'broken', 'sold'])->default('inactive');

            // Timestamps
            $table->timestamp('setup_at')->nullable();            
            $table->timestamp('last_maintenance_at')->nullable();
            $table->timestamp('last_broken_at')->nullable();

            // Foreign keys
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');

            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_machines');
    }
};
