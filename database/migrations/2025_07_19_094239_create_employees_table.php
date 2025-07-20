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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->decimal('salary_month', 15, 3)->default(0);
            $table->decimal('current_mood', 15, 3)->default(0);
            $table->decimal('mood_decay_rate_days', 15, 3)->default(0);
            $table->decimal('efficiency_factor', 15, 3)->default(0);

            $table->enum('status', ['active', 'inactive', 'fired', 'resigned','applied'])->default('applied');

            // Timestamps
            $table->timestamp('applied_at')->nullable();
            $table->integer('timelimit_days')->default(1);

            $table->timestamp('hired_at')->nullable();
            $table->timestamp('fired_at')->nullable();
            $table->timestamp('resigned_at')->nullable();
            $table->timestamp('last_promotion_at')->nullable();

            // Foreign keys
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_profile_id')->constrained()->onDelete('cascade');
                        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
