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
        Schema::create('school_class_bookings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('user_id');

            $table->timestamp('booked_at');
            $table->string('timezone')->nullable();

            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_class_bookings');
    }
};
