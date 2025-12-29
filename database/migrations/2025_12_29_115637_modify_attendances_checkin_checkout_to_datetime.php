<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change check_in and check_out from TIME to DATETIME
        DB::statement('ALTER TABLE attendances MODIFY check_in DATETIME NULL');
        DB::statement('ALTER TABLE attendances MODIFY check_out DATETIME NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to TIME
        DB::statement('ALTER TABLE attendances MODIFY check_in TIME NULL');
        DB::statement('ALTER TABLE attendances MODIFY check_out TIME NULL');
    }
};
