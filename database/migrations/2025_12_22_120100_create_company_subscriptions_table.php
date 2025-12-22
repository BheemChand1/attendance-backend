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
        Schema::create('company_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained()->onDelete('restrict');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'cancelled', 'paused', 'expired'])->default('active');
            $table->decimal('price', 10, 2); // Price at the time of subscription
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->date('next_billing_date')->nullable();
            $table->integer('employee_count')->default(0); // Track current employee count
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['company_id', 'status']);
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_subscriptions');
    }
};
