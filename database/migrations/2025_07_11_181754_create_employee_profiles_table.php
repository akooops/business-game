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
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('skills')->nullable();

            // Salary
            $table->float('monthly_min_salary', 13, 3);
            $table->float('monthly_avg_salary', 13, 3);
            $table->float('monthly_max_salary', 13, 3);

            // Recruitment
            $table->enum('recruitment_difficulty', ['very_easy', 'easy', 'medium', 'hard', 'very_hard'])->default('medium');
            $table->float('recruitment_cost_per_employee', 13, 3);

            // Training
            $table->float('training_cost_per_employee', 13, 3);
            $table->integer('training_duration_days');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};
