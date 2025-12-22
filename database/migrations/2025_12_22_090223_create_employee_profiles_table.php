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
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();
            
            // Personal Information
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('employee_photo')->nullable();
            
            // Address Information
            $table->string('street_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            
            // Employment Information
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->date('joining_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave', 'terminated'])->default('active');
            
            // Education Information
            $table->string('qualification')->nullable();
            $table->string('specialization')->nullable();
            $table->string('university')->nullable();
            $table->year('graduation_year')->nullable();
            
            // Documents (stored as JSON array of file paths)
            $table->json('documents')->nullable();
            
            $table->timestamps();
            
            // One profile per user
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};
