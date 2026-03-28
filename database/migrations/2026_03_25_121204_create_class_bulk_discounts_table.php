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
        Schema::create('class_bulk_discounts', function (Blueprint $table) {
           	$table->id();

			$table->string('title');
			$table->string('text')->nullable();

            // Owner (School or Tutor)
            $table->unsignedBigInteger('owner_id');
            $table->string('owner_type'); // 'school' or 'tutor'

            // Bulk logic
            $table->integer('min_quantity');
            $table->integer('max_quantity')->nullable();

            $table->decimal('discount_percentage', 5, 2);

            // Optional
            $table->boolean('is_active')->default(true);            

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_bulk_discounts');
    }
};
