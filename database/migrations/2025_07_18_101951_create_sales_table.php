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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            // Timelimit 
            $table->integer('gameweek')->default(1);
            $table->integer('timelimit_days')->default(1);

            // Quantity
            $table->decimal('quantity', 15, 3)->default(0);

            // Pricing
            $table->decimal('sale_price', 15, 3)->default(0);
            $table->decimal('shipping_cost', 15, 3)->default(0);

            // Shipping 
            $table->integer('shipping_time_days')->default(1);

            // Status
            $table->enum('status', ['initiated', 'confirmed', 'delivered', 'cancelled'])->default('initiated');

            // Timestamps
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // Foreign keys
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('wilaya_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
