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
        Schema::table('courses', function (Blueprint $table) {
            //
           // Use foreignId + constrained + nullOnDelete (compatible & clean)
            $table->foreignId('category_level_four_id')
                  ->nullable()
                  ->after('sub_sub_category_id') // adjust as needed
                  ->constrained('category_level_fours')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_level_four_id');
        });
    }
};
