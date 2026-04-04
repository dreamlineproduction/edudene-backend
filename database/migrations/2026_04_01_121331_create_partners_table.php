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
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
			$table->string('name')->nullable();
			$table->foreignId('school_id')->constrained()->onDelete('cascade');
			$table->enum('type',['Member','Student','Employee','Others']);
			$table->string('discount_title')->nullable();
			$table->enum('discount_type',['percent','fixed']);
			$table->double('discount_amount')->nullable();
			$table->string('discount_category'); // Express, Diploma, Courses [multiselect]
			$table->date('start_date')->nullable();
			$table->date('end_date')->nullable();
			$table->enum('status',['Active', 'Inactive' ])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
