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
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
			$table->string('name');
			$table->enum('interval',['monthly','quarterly','yearly'])->default('monthly');
			$table->double('price',10,2);
			$table->enum('status',['active','block'])->default('active');
			$table->enum('user_type',['school','tutor','student'])->default('school');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_plans');
    }
};
