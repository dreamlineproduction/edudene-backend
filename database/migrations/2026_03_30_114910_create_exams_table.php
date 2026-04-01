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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
			$table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
			$table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
			$table->integer('enable');
			$table->integer('no_of_questions');
			$table->integer('total_exam_marks');
			$table->integer('min_pass_marks');
			$table->integer('duration');
			$table->decimal('retake_fee',10,2);
			$table->date('expiry_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
