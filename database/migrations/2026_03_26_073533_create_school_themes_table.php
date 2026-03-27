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
        Schema::create('school_themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();

            $table->string('primary_color')->nullable();
            $table->string('primary_hover_color')->nullable();
            $table->string('primary_outline_color')->nullable();
            $table->string('primary_outline_hover_color')->nullable();
            $table->string('secondary_color')->nullable();
            $table->string('secondary_hover_color')->nullable();
            $table->string('secondary_outline_color')->nullable();
            $table->string('secondary_outline_hover_color')->nullable();

            $table->string('banner_image')->nullable();
            $table->string('banner_image_url')->nullable();
            $table->string('logo_image')->nullable();
            $table->string('logo_image_url')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_themes');
    }
};
