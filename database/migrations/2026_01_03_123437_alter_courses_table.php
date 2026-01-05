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
        // Add reason column
        Schema::table('courses', function (Blueprint $table) {
            $table->text('reason')->nullable()->after('status');
        });

        // Modify enum values (MySQL)
        DB::statement("
            ALTER TABLE courses 
            MODIFY status ENUM('Active', 'Inactive', 'Draft', 'Pending', 'Decline') 
            NOT NULL DEFAULT 'Draft'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove reason column
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('reason');
        });

        // Revert enum values
        DB::statement("
            ALTER TABLE courses 
            MODIFY status ENUM('Active', 'Inactive', 'Draft') 
            NOT NULL DEFAULT 'Draft'
        ");
    }
};
