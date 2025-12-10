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
            //
            $table->dropColumn('vimeo_url');
            $table->dropColumn('x_url');
            $table->dropColumn('youtube_url');
            $table->dropColumn('instagram_url');
            $table->dropColumn('linkedin_url');
            $table->dropColumn('facebook_url');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
           
        });
    }
};
