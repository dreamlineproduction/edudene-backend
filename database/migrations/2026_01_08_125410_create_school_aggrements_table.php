<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_aggrements', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('user_id');

            $table->enum('ip_agreement',['Yes','No'])->default('No');
            $table->string('agreement_file')->nullable();
            $table->string('agreement_file_url')->nullable();
            $table->enum('is_freelancer',['Yes','No'])->default('No');

            $table->timestamps();

            // Foreign Key
            $table->foreign('school_id')
                  ->references('id')
                  ->on('schools')
                  ->onDelete('cascade');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_aggrements');
    }
};
