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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('tutor_id')->nullable()->constrained('users')->cascadeOnDelete();

            
            $table->string('item_type', 50)->nullable();    
            $table->integer('item_id')->nullable();
            $table->string('model_name')->nullable();

            $table->string('title')->nullable();


            $table->decimal('tutor_revenue', 10, 2)->nullable();  
            $table->decimal('school_revenue', 10, 2)->nullable();  
            $table->decimal('admin_revenue')->nullable();  

            $table->decimal('vat_rate', 5, 2)->nullable();  
            $table->decimal('vat_amount', 10, 2)->nullable();  

            $table->decimal('price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();  
            $table->integer('quantity')->default(1);

            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
