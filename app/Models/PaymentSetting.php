<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    //
    protected $fillable = [
        'stripe_status', 
        'stripe_secret_key',
        'stripe_public_key',
        'test_stripe_secret_key',
        'test_stripe_public_key',
        'stripe_use',
        'course_commission',
        'corporate_commission',
        'personal_tutor_commission',
        'school_course_commission',
        'school_class_commission',
        'vat_tax_commission',  
        'created_at',
        'updated_at'
    ]; 


    protected $hidden = [
        'created_at',
        'updated_at'
    ]; 
}
