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
        Schema::table('payment_settings', function (Blueprint $table) {
            //
            $table->enum('stripe_status', ['Active', 'Inactive'])->default('Active')->comment('Active,Inactive')->after('id');

            $table->tinyInteger('course_commission')->after('stripe_use')->nullable();
            $table->tinyInteger('corporate_commission')->after('course_commission')->nullable();
            $table->tinyInteger('personal_tutor_commission')->after('corporate_commission')->nullable();
            $table->tinyInteger('school_course_commission')->after('personal_tutor_commission')->nullable();
            $table->tinyInteger('school_class_commission')->after('school_course_commission')->nullable();
            $table->tinyInteger('vat_tax_commission')->after('school_class_commission')->nullable();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            //
            $table->dropColumn([
                'stripe_status', 
                'course_commission', 
                'corporate_commission', 
                'personal_tutor_commission',
                'school_course_commission', 
                'school_class_commission', 
                'vat_tax_commission'
            ]);
        });
    }
};
