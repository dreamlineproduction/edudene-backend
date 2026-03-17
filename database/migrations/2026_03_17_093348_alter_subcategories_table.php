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
		Schema::table('sub_categories', function (Blueprint $table) {
			$table->boolean('is_popular')->after('status')->default(false);
    		$table->integer('popular_order')->after('is_popular')->nullable();
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       	Schema::table('sub_categories', function (Blueprint $table) {
			$table->dropColumn('is_popular');
    		$table->dropColumn('popular_order');
		});
    }
};
