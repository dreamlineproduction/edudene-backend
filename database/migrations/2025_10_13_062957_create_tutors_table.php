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
        Schema::create('tutors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->text('about')->nullable();
            $table->integer('year_of_experience')->nullable();
            $table->integer('passing_year')->nullable();
            $table->string('university', 150)->nullable();
            $table->string('highest_qualification', 150)->nullable();
            $table->string('address_line_1', 255)->nullable();
            $table->string('address_line_2', 255)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('x_url', 150)->nullable();
            $table->string('facebook_url', 150)->nullable();
            $table->string('linkedin_url', 150)->nullable();
            $table->string('avatar', 150)->nullable();
            $table->string('police_certificate', 150)->nullable();
            $table->string('experience_letter', 150)->nullable();
            $table->string('qualification_certificate', 150)->nullable();
            $table->string('video_type', 150)->nullable();
            $table->string('video_url', 150)->nullable();
            $table->string('video', 150)->nullable();
            $table->string('video_poster', 150)->nullable();
            $table->enum('is_admin_verified', ['Yes', 'No'])->default('No');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutors');
    }
};
