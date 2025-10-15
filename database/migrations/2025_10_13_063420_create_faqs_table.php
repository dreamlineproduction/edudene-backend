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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();            
            $table->unsignedBigInteger('faq_section_id')->nullable();
            $table->string('title',200)->nullable();
            $table->longText('description')->nullable();
            $table->enum('status',['Active', 'Inactive'])->default('Active')->comment('Active,Inactive');
            $table->timestamps();

            $table->foreign('faq_section_id')
                ->references('id')
                ->on('faq_sections')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
