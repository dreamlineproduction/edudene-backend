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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->string('payment_intent_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('card_brand')->nullable();     
            $table->string('card_last4')->nullable();   

            $table->decimal('vat_rate', 5, 2)->default(0);   
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);

            $table->string('pay_with')->default('STRIPE'); 
            $table->string('status')->default('Pending'); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
