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
            $table->string('stripe_email')->after('school_slug')->nullable();
            $table->string('facebook')->after('website')->nullable();
            $table->string('instagram')->after('facebook')->nullable();
            $table->string('linkedin')->after('instagram')->nullable();
            $table->string('youtube')->after('linkedin')->nullable();
            $table->string('x')->after('youtube')->nullable();
            $table->string('vimeo')->after('x')->nullable();
            $table->string('pinterest')->after('vimeo')->nullable();
            $table->string('github')->after('pinterest')->nullable();
            $table->string('logo')->after('github')->nullable();
            $table->string('logo_url')->after('logo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            //
            $table->dropColumn([
                'stripe_email',
                'facebook',
                'instagram',
                'linkedin',
                'youtube',
                'x',
                'vimeo',
                'pinterest',
                'github',
                'logo',
                'logo_url',
            ]);
        });
    }
};
