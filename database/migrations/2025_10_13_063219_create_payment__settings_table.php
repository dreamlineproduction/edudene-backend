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
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_secret_key',150)->nullable();
            $table->string('stripe_public_key',150)->nullable();
            $table->string('test_stripe_secret_key',150)->nullable();
            $table->string('test_stripe_public_key',150)->nullable();
            $table->enum('stripe_use',['Test','Live'])->default('Test')->comment('Test,Live');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
