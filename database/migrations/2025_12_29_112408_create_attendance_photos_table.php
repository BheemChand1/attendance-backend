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
        Schema::create('attendance_photos', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('attendance_id')
                ->constrained('attendances')
                ->cascadeOnDelete();
            
            $table->enum('type', ['check_in', 'check_out']);
            
            $table->string('photo_path', 255);
            
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            $table->timestamp('created_at')->nullable();
            
            $table->index('attendance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_photos');
    }
};
