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
        Schema::table('school_themes', function (Blueprint $table) {
            //
            $table->string('primary_text_color')->nullable()->after('primary_color');
            $table->string('primary_hover_text_color')->nullable()->after('primary_hover_color');
            $table->string('primary_outline_text_color')->nullable()->after('primary_outline_color');
            $table->string('primary_outline_hover_text_color')->nullable()->after('primary_outline_hover_color');
            $table->string('secondary_text_color')->nullable()->after('secondary_color');
            $table->string('secondary_hover_text_color')->nullable()->after('secondary_hover_color');
            $table->string('secondary_outline_text_color')->nullable()->after('secondary_outline_color');
            $table->string('secondary_outline_hover_text_color')->nullable()->after('secondary_outline_hover_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_themes', function (Blueprint $table) {
            Schema::table('school_themes', function (Blueprint $table) {
                $table->dropColumn([
                    'primary_text_color', 
                    'primary_hover_text_color',
                    'primary_outline_text_color',
                    'primary_outline_hover_text_color',
                    'secondary_text_color',
                    'secondary_hover_text_color',
                    'secondary_outline_text_color',
                    'secondary_outline_hover_text_color'
                ]);
            });
        });
    }
};
