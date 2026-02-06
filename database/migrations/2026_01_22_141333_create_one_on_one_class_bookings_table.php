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
        Schema::create('one_on_one_class_bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('slot_id')
                  ->constrained('one_on_one_class_slots')
                  ->cascadeOnDelete();

            $table->foreignId('student_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->enum('status', ['pending', 'confirmed', 'cancelled'])
                  ->default('pending');

            $table->timestamp('booked_at')->useCurrent();
			$table->string('timezone')->nullable();
            $table->timestamps();

            // One booking per slot
            $table->unique('slot_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_on_one_class_bookings');
    }
};
