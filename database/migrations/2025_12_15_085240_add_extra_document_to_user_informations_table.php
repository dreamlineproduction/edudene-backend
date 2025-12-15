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
        Schema::table('user_informations', function (Blueprint $table) {
            //
            $table->string('id_type',150)->after('country')->nullable();
            $table->string('front_side_document')->after('id_type')->nullable();
            $table->string('front_side_document_url')->after('front_side_document')->nullable();
            $table->string('back_side_document')->after('front_side_document_url')->nullable();
            $table->string('back_side_document_url')->after('back_side_document')->nullable();
            $table->string('face_image')->after('back_side_document_url')->nullable();
            $table->string('face_image_url')->after('face_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_informations', function (Blueprint $table) {
            //
            $table->dropColumn('front_side_document');
            $table->dropColumn('front_side_document_url');
            $table->dropColumn('back_side_document');
            $table->dropColumn('back_side_document_url');
            $table->dropColumn('face_image');
            $table->dropColumn('face_image_url');
        });
    }
};
