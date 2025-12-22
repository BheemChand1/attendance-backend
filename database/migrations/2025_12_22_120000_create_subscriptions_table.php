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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Basic, Professional, Enterprise
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2); // Monthly price
            $table->integer('max_employees'); // Maximum employees allowed
            $table->integer('max_departments')->nullable(); // Maximum departments
            $table->integer('storage_gb')->default(5); // Storage in GB
            $table->integer('support_level')->default(1); // 1: Email, 2: Priority, 3: 24/7
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
