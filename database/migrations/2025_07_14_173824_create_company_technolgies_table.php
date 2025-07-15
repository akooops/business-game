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
        Schema::create('company_technologies', function (Blueprint $table) {
            $table->id();

            $table->decimal('research_cost', 15, 3)->default(0);
            $table->integer('research_time_days')->default(0);

            $table->timestamp('started_at')->nullable();
            
            $table->timestamp('estimated_completed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('technology_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_technolgies');
    }
};
