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
        Schema::create('user_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate()->nullable();
            $table->enum('type',['Face','IDProof','Both'])->nullable();
            $table->string('front_side_document')->nullable();
            $table->string('front_side_document_url')->nullable();
            $table->string('back_side_document')->nullable();
            $table->string('back_side_document_url')->nullable();
            $table->string('face_image')->nullable();
            $table->string('face_image_url')->nullable();
            $table->enum('status',['Pending','Approved','Declined'])->default('Pending');
            $table->string('decline_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_verifications');
    }
};
