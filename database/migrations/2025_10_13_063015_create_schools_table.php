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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->tinyText('school_name')->nullable();
            $table->string('phone_number',100)->nullable();            
            $table->string('about_us',150)->nullable();
            $table->string('registration_no',20)->nullable();
            $table->tinyText('address_line_1')->nullable();
            $table->tinyText('address_line_2')->nullable();
            $table->tinyText('zip')->nullable();
            $table->string('city',50)->nullable();
            $table->string('state',100)->nullable();
            $table->string('country',100)->nullable();
            $table->string('website_url',150)->nullable();
            $table->string('facebook_url',150)->nullable();
            $table->string('linkedin_url',150)->nullable();
            $table->string('instagram_url',150)->nullable();
            $table->string('youtube_url',150)->nullable();
            $table->string('x_url',150)->nullable();          
            $table->string('vimeo_url',150)->nullable();
            $table->string('pinterest_url',150)->nullable();            
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')
            ->onDelete('cascade')
            ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
