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
        Schema::create('technologies', function (Blueprint $table) {
            $table->id();

            // Product details
            $table->string('name');
            $table->text('description')->nullable();

            // Technology level
            $table->integer('level')->default(0);

            // Technology research
            $table->decimal('research_cost', 15, 3)->default(0);
            $table->integer('research_time_days')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technologies');
    }
};
