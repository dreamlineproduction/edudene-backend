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
        Schema::create('subject_requests', function (Blueprint $table) {
            $table->id();
			$table->foreignId('category_id')->constrained()->onDelete('cascade');
			$table->foreignId('sub_category_id')->constrained()->onDelete('cascade');
			$table->foreignId('sub_sub_category_id')->constrained()->onDelete('cascade');
			$table->foreignId('user_id')->constrained()->onDelete('cascade');
			$table->string('subject');
			$table->enum('status',['Active','Pending','Reject']);			
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_request');
    }
};
