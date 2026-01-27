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
       Schema::table('classes', function (Blueprint $table) {
            $table->text('description')->nullable()->after('price');
            $table->string('cover_image')->nullable()->after('description');
            $table->string('cover_image_url')->nullable()->after('cover_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'cover_image',
                'cover_image_url'
            ]);
        });
    }
};
