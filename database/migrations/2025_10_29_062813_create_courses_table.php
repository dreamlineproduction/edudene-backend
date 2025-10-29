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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_type_id')->constrained('course_types')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->cascadeOnDelete();
            $table->foreignId('sub_sub_category_id')->nullable()->constrained('sub_sub_categories')->cascadeOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('countries')->cascadeOnDelete();
            $table->foreignId('state_id')->nullable()->constrained('states')->cascadeOnDelete();
            $table->string('title', 200)->nullable();
            $table->string('slug',200)->nullable()->unique();
            $table->tinyText('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('level', 100)->nullable();
            $table->double('price')->default(0);
            $table->double('discount_price')->nullable();
            $table->enum('status', ['Active', 'Inactive', 'Draft'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
