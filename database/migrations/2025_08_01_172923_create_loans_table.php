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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();

            $table->decimal('amount', 15, 3);
            $table->integer('duration_months');
            $table->decimal('interest_rate', 15, 3);
            $table->decimal('total_amount', 15, 3);
            $table->decimal('monthly_payment', 15, 3);

            $table->decimal('remaining_amount', 15, 3);

            $table->timestamp('borrowed_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->foreignId('bank_id')->constrained('banks')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
