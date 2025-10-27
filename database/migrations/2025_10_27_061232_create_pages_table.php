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
        Schema::table('pages', function (Blueprint $table) {           
              $table->enum('is_show',['Header','Footer','Both'])->after('meta_keyword')->default('Both');
        });      
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('pages', function (Blueprint $table) {
            //
            $table->dropColumn('is_show');
        });
    }
};
