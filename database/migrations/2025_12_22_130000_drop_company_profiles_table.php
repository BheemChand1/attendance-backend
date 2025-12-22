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
        Schema::dropIfExists('company_profiles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();
            $table->string('designation')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
            $table->unique('user_id');
        });
    }
};
