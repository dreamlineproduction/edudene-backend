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
        Schema::create('one_on_one_class_slots', function (Blueprint $table) {
            $table->id();

            // If you have tutors
            $table->foreignId('tutor_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->date('class_date');
            $table->time('start_time');
            $table->time('end_time');
			$table->boolean('is_free_trial')->default(false);
            $table->boolean('is_active')->default(true);
			$table->string('timezone')->nullable();

            $table->timestamps();

            // Prevent duplicate slots
            $table->unique(['tutor_id', 'class_date', 'start_time', 'end_time'], 'unique_tutor_slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_on_one_class_slots');
    }
};
