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

            // Salary
            $table->decimal('min_salary_month', 15, 3)->default(0);
            $table->decimal('avg_salary_month', 15, 3)->default(0);
            $table->decimal('max_salary_month', 15, 3)->default(0);

            // Recruitment
            $table->decimal('min_recruitment_cost', 15, 3)->default(0);
            $table->decimal('avg_recruitment_cost', 15, 3)->default(0);
            $table->decimal('max_recruitment_cost', 15, 3)->default(0);
            $table->decimal('real_recruitment_cost', 15, 3)->default(0);

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
