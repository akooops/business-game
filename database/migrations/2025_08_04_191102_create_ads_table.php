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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();

            $table->decimal('price', 15, 3)->default(0);
            $table->integer('duration_days')->default(0);
            $table->decimal('market_impact_percentage', 15, 3)->default(0);

            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->foreignId('advertiser_id')->constrained('advertisers');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('company_id')->constrained('companies');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
