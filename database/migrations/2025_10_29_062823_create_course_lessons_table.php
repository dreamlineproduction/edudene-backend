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
        Schema::create('course_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('course_chapter_id')->constrained('course_chapters')->cascadeOnDelete();
            $table->enum('type', ['Youtube', 'Vimeo', 'Video','Image','Document','VideoUrl'])->nullable();
            $table->string('video_url', 255)->nullable();
            $table->string('video', 255)->nullable();
            $table->string('image', 255)->nullable()->comment('for image type lesson and video poster');
            $table->string('document', 255)->nullable()->comment('pdf, doc, docx etc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_lessons');
    }
};
