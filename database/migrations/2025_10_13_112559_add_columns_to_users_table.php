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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('id');
            $table->string('user_name',150)->nullable()->after('email');
            $table->dateTime('last_login_datetime')->nullable()->after('password');
            $table->string('timezone',100)->nullable()->after('last_login_datetime');
            $table->string('avatar',200)->nullable()->after('timezone');
            $table->enum('status',['Active','Inactive'])->default('Inactive')->after('avatar');
            $table->enum('online_status',['Visible','Invisible'])->default('Visible')->after('status');
            $table->enum('enable_2fa_authcation',['Yes','No'])->default('No')->after('online_status');

            $table->renameColumn('name', 'full_name');
            $table->foreign('role_id')->references('id')->on('roles')
                ->onDelete('cascade')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
