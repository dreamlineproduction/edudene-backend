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
        Schema::table('tutors', function (Blueprint $table) {
            $table->string('what_i_teach')->nullable()->after('highest_qualification');
            $table->string('education')->nullable()->after('what_i_teach');
            $table->string('language')->nullable()->after('education');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutors', function (Blueprint $table) {
            //
            $table->dropColumn('what_i_teach');
            $table->dropColumn('education');
            $table->dropColumn('language');
        });
    }
};
