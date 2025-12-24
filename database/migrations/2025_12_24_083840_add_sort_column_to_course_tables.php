<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('course_requirements', function (Blueprint $table) {
            $table->integer('sort')->default(0)->after('title');
        });

        Schema::table('course_outcomes', function (Blueprint $table) {
            $table->integer('sort')->default(0)->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('course_requirements', function (Blueprint $table) {
            $table->dropColumn('sort');
        });

        Schema::table('course_outcomes', function (Blueprint $table) {
            $table->dropColumn('sort');
        });
    }
};

