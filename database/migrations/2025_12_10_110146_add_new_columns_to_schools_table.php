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

        Schema::table('schools', function (Blueprint $table) {
            $table->string('school_slug')->after('school_name')->nullable();
            $table->string('year_of_registration')->after('registration_number')->nullable();
            $table->string('license_type')->after('year_of_registration')->nullable();
            $table->string('tax_details')->after('license_type')->nullable();
            $table->string('school_document')->after('tax_details')->nullable();
            $table->string('school_document_url')->after('school_document')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            //
             $table->dropColumn('school_slug');
            $table->dropColumn('year_of_registration');
            $table->dropColumn('license_type');
            $table->dropColumn('tax_details');
            $table->dropColumn('school_document');
            $table->dropColumn('school_document_url');
        });
    }
};
