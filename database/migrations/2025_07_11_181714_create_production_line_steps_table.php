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
        Schema::create('production_line_steps', function (Blueprint $table) {
            $table->id();

            // Name and description of the step 
            $table->string('name');
            $table->text('description')->nullable();

            // Step order/sequence number
            $table->integer('step');

            // Foreign keys
            $table->foreignId('production_line_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_line_steps');
    }
};
