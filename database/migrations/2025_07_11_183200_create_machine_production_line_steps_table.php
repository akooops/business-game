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
        Schema::create('machine_production_line_steps', function (Blueprint $table) {
            $table->id();

            // Time required to setup machine for this production step in days
            $table->integer('setup_time_days');

            // Foreign keys
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->foreignId('production_line_step_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_production_line_steps');
    }
};
