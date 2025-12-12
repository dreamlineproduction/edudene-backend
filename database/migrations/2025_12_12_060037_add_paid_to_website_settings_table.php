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
        Schema::table('website_settings', function (Blueprint $table) {
            //
            $table->string('linkedin_url')->after('ipinfo_token')->nullable();
            $table->string('facebook_url')->after('linkedin_url')->nullable();
            $table->string('instagram_url')->after('facebook_url')->nullable();
            $table->string('x_url')->after('instagram_url')->nullable();
            $table->string('youtube_url')->after('x_url')->nullable();
            $table->string('pinterest_url')->after('youtube_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('website_settings', function (Blueprint $table) {
            //
            $table->dropColumn('linkedin_url');
            $table->dropColumn('facebook_url');
            $table->dropColumn('instagram_url');
            $table->dropColumn('x_url');
            $table->dropColumn('youtube_url');
            $table->dropColumn('pinterest_url');
        });
    }
};
