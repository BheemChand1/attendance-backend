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
        Schema::table('users', function (Blueprint $table) {
            // Company (NULL for superadmin)
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->nullOnDelete();

                // Role
            $table->foreignId('role_id')
                ->after('company_id')
                ->constrained('roles')
                ->cascadeOnDelete();

                // Extra fields
            $table->string('phone')->nullable()->after('password');
            $table->string('employee_code')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('employee_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
             // Drop FKs first
            $table->dropForeign(['company_id']);
            $table->dropForeign(['role_id']);

            // Drop columns
            $table->dropColumn([
                'company_id',
                'role_id',
                'phone',
                'employee_code',
                'is_active',
            ]);
        });
    }
};
