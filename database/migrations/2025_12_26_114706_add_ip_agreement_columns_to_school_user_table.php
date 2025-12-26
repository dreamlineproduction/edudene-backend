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
         Schema::table('school_user', function (Blueprint $table) {

            $table->enum('ip_agreement',['Yes','No'])->default('No')->after('user_id');

            $table->string('agreement_file')->nullable()->after('ip_agreement');

            $table->string('agreement_file_url')->nullable()->after('agreement_file');

            $table->enum('is_freelancer',['Yes','No'])->default('No')->after('agreement_file_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_user', function (Blueprint $table) {
            //
            $table->dropColumn([
                'ip_agreement',
                'agreement_file',
                'agreement_file_url',
                'is_freelancer',
            ]);
        });
    }
};
