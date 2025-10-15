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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title',200)->nullable();
            $table->string('slug',200)->nullable();
            $table->longText('description')->nullable();
            $table->string('meta_title',200)->nullable();
            $table->longText('meta_description')->nullable();
            $table->string('meta_keyword',200)->nullable();
            $table->enum('status',['Active', 'Inactive'])->default('Active')->comment('Active,Inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
