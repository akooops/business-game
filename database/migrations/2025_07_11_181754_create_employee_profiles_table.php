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
            $table->decimal('min_salary_month', 15, 3)->default(0);
            $table->decimal('avg_salary_month', 15, 3)->default(0);
            $table->decimal('max_salary_month', 15, 3)->default(0);

            // Recruitment
            $table->enum('recruitment_difficulty', ['very_easy', 'easy', 'medium', 'hard', 'very_hard'])->default('medium');
            $table->decimal('recruitment_cost_per_employee', 15, 3)->default(0);

            // Training
            $table->decimal('training_cost_per_employee', 15, 3)->default(0);
            $table->integer('training_duration_days')->default(1);
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
