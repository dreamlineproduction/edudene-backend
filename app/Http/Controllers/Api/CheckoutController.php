<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class CheckoutController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $amount = $request->amount;

        $paymentIntent = PaymentIntent::create([
            'amount' => $amount * 100,
            'currency' => 'usd',
            'metadata' => [
                'user_id' => auth('sanctum')->id(),
            ],
        ]);


        return jsonResponse(true,'Stripe client secret',[
            'clientSecret' => $paymentIntent->client_secret
        ]);        
    }
}
