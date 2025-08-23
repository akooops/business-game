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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();

            $table->enum('movement_type', ['in', 'out', 'expired', 'damaged', 'lost']);

            $table->decimal('original_quantity', 10, 3)->nullable();
            $table->decimal('current_quantity', 10, 3)->nullable();

            $table->text('notes')->nullable(); 
            $table->timestamp('moved_at')->nullable();
            
            $table->nullableMorphs('reference'); 

            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
