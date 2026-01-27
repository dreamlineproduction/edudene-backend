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
       Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            // relation with cart
            $table->unsignedBigInteger('cart_id');

            // product identification
            $table->string('item_type', 50);  
            $table->unsignedBigInteger('item_id');

            // snapshot data
            $table->string('title');
            $table->decimal('price', 10, 2);

            $table->integer('qty')->default(1);

            // optional extra info
            $table->json('metadata')->nullable();

            $table->timestamps();

            // FK only for cart
            $table->foreign('cart_id')
                ->references('id')
                ->on('carts')
                ->cascadeOnDelete();

            $table->unique(
                ['cart_id', 'item_type', 'item_id'],
                'cart_item_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
