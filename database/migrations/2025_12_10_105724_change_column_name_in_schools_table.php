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
            $table->renameColumn('registration_no', 'registration_number');
            $table->renameColumn('website_url', 'website');
            $table->renameColumn('pinterest_url', 'social_media');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            //
            $table->renameColumn('registration_number', 'registration_no');
            $table->renameColumn('website', 'website_url');
            $table->renameColumn('social_media', 'pinterest_url');
        });
    }
};
