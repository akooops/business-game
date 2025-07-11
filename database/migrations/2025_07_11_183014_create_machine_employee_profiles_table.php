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
        Schema::create('machine_employee_profiles', function (Blueprint $table) {
            $table->id();

            // Number of employees with this profile required per machine
            $table->integer('required_count')->default(1);

            // Foreign keys
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_profile_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_employee_profiles');
    }
};
