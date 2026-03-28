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
        Schema::create('school_invitations', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('email')->nullable();
            $table->string('token')->unique()->nullable();

            $table->enum('status', ['Invited', 'Accepted', 'Rejected'])->nullable();
            $table->timestamp('accepted_at')->nullable();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_invitations');
    }
};
