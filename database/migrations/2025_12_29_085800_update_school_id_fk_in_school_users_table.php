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
        Schema::table('school_users', function (Blueprint $table) {
            //
            $table->dropForeign('school_user_school_id_foreign');

            $table->dropColumn('school_id');

            $table->foreignId('school_id')->after('user_id');

            $table->foreign('school_id', 'fk_school_users_school_id')
                ->references('id')
                ->on('schools')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_users', function (Blueprint $table) {
            //
            $table->dropForeign(['school_id']);

             $table->foreign('school_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
