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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_international')->default(false);
            $table->decimal('research_cost', 10, 5)->default(0);
            
            // Location relationships - one or the other based on type
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('wilaya_id')->nullable()->constrained()->onDelete('cascade');
            
            $table->timestamps();
            
            // Ensure only one location type is set
            $table->index(['is_international', 'country_id']);
            $table->index(['is_international', 'wilaya_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
}; 