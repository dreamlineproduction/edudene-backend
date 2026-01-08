<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('school_users', function (Blueprint $table) {
            $table->dropColumn([
                'ip_agreement',
                'agreement_file',
                'agreement_file_url',
                'is_freelancer',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('school_users', function (Blueprint $table) {
            $table->string('ip_agreement')->nullable();
            $table->string('agreement_file')->nullable();
            $table->string('agreement_file_url')->nullable();
            $table->boolean('is_freelancer')->default(false);
        });
    }
};

