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
        Schema::create('school_sub_sub_category', function (Blueprint $table) {
           	$table->id();
			$table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
			$table->foreignId('category_id')->constrained('sub_sub_categories')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_sub_sub_category');
    }
};
