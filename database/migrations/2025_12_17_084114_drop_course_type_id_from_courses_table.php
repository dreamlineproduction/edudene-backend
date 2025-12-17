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
        Schema::table('courses', function (Blueprint $table) {         
            $table->dropForeign(['course_type_id']);   
            $table->dropColumn('course_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {            
             $table->unsignedBigInteger('course_type_id')->nullable();
            $table->foreign('course_type_id')
                ->references('id')
                ->on('course_types')
                ->onDelete('cascade');
        });
    }
};
