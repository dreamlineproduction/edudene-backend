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
        Schema::table('files', function (Blueprint $table) {            
            $table->string('poster_name')->nullable()->after('url')->comment('Useing Video Case');
            $table->string('poster_path')->nullable()->after('poster_name')->comment('Useing Video Case');;
            $table->string('poster_url')->nullable()->after('poster_path')->comment('Useing Video Case');;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn(['poster_name', 'poster_path', 'poster_url']);
        });
    }
};
