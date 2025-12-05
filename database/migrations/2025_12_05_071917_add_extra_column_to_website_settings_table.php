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
        Schema::table('website_settings', function (Blueprint $table) {    
            $table->string('dark_logo_url',150)->after('dark_logo')->nullable();
            $table->string('light_logo_url',150)->after('dark_logo_url')->nullable();         
            $table->string('favicon_logo_url',150)->after('light_logo_url')->nullable();         
            $table->string('name',150)->after('favicon_logo')->nullable();
            $table->string('keywords',200)->after('name')->nullable();
            $table->tinyText('description')->after('keywords')->nullable();
            $table->string('author',150)->after('description')->nullable();
            $table->string('slogan',150)->after('author')->nullable();
            $table->string('system_email',150)->after('slogan')->nullable();
            $table->string('address')->after('system_email')->nullable();
            $table->string('phone_number',15)->after('address')->nullable();
            $table->string('agora_app_id',200)->after('phone_number')->nullable();
            $table->string('agora_certificate',200)->after('agora_app_id')->nullable();
            $table->string('ipinfo_token',200)->after('agora_certificate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('website_settings', function (Blueprint $table) {
            //
            $table->dropColumn('dark_logo_url');
            $table->dropColumn('light_logo_url');
            $table->dropColumn('favicon_logo_url');
            $table->dropColumn('name');
            $table->dropColumn('keywords');
            $table->dropColumn('description');
            $table->dropColumn('author');
            $table->dropColumn('slogan');
            $table->dropColumn('system_email');
            $table->dropColumn('address');
            $table->dropColumn('phone_number');
            $table->dropColumn('agora_app_id');
            $table->dropColumn('agora_certificate');
            $table->dropColumn('ipinfo_token');

        });
    }
};
