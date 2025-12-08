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
            //
            $table->string('police_certificate_url')->after('police_certificate')->nullable();
            $table->string('experience_letter_url')->after('police_certificate_url')->nullable();
            $table->string('qualification_certificate_url')->after('experience_letter_url')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutors', function (Blueprint $table) {
            //
            $table->dropColumn('police_certificate_url');
            $table->dropColumn('experience_letter_url');
            $table->dropColumn('qualification_certificate_url');
        });
    }
};
