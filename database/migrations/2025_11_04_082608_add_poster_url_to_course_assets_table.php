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
        Schema::table('course_assets', function (Blueprint $table) {
            Schema::table('course_assets', function (Blueprint $table) {
                $table->string('poster_url')->nullable()->after('poster');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_assets', function (Blueprint $table) {
            $table->dropColumn('poster_url');
        });
    }
};
