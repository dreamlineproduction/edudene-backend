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
            $table->string('footer_text')->after('ipinfo_token')->nullable();
            $table->string('footer_link')->after('footer_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('website_settings', function (Blueprint $table) {
            //
            $table->dropColumn('footer_text');
            $table->dropColumn('footer_link');
        });
    }
};
