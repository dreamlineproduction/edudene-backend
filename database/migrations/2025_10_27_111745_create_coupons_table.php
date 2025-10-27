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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['Fixed', 'Percentage'])->default('Fixed');
            $table->integer('amount')->nullable();
            $table->double('percentage',10,2)->nullable();
            $table->date('validity')->nullable();
            $table->string('batch_number',150)->nullable();
            $table->enum('is_redeem', ['Yes', 'No'])->default('no');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
