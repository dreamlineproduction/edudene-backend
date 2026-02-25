<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use Stripe\Stripe;
use Stripe\PaymentIntent;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    //

    /**
     * User orders list
     */
    public function index()
    {
        return Order::with('items')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();
    }

    /**
     * Single order detail
     */
    public function show($orderNumber = null)
    {
        $user = auth('sanctum')->user();

        $order = Order::where(['user_id'=>$user->id,'order_number'=>$orderNumber])->first();

        if(empty($order))    
        {
             return jsonResponse(false, 'Order not found in our database.', 404);
        }

        return jsonResponse(true, 'Order data.', [
            'order' => $order
        ]);
    }

    /**
     * Create order after payment success
     */
    public function store(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        DB::beginTransaction();

        try {

            $user = auth('sanctum')->user();

            $cart = Cart::where(['id' => $request->cart_id, 'user_id' => $user->id])->first();
            if (!$cart) {
                return jsonResponse(false, 'Cart Invalid', 404);
            }

            // Carts data
            $cartItems = CartItem::where('cart_id', $request->cart_id)->get();

            if ($cartItems->isEmpty()) {
                return jsonResponse(false, 'Cart is empty', 400);
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);
            $charge = $paymentIntent->charges->data[0] ?? null;
            $card = $charge?->payment_method_details?->card;


            $subtotal = $cartItems->sum(fn ($item) =>
                $item->price * $item->qty
            );

            $total = $cartItems->sum(fn ($item) =>
                $item->discount_price * $item->qty
            );

            $discount = $subtotal - $total;

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'payment_intent_id' => $request->payment_intent_id,
                'payment_method' => 'card',
                'card_brand' => $card?->brand,
                'card_last4' => $card?->last4,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'status' => 'Paid',
            ]);

            foreach ($cartItems as $item) {

                $metadata = $item->metadata ?? [];

                $orderItemCreate = [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'item_type' => $item->item_type,
                    'item_id' => $item->item_id,
                    'model_name' => $item->model_name,
                    'title' => $item->title,
                    'price' => $item->price,
                    'discount_price' => $item->discount_price,
                    'quantity' => $item->quantity ?? 1,
                    'metadata' => $metadata,
                ];

                if (in_array($item->item_type, ['TUTOR_SLOT', 'TUTOR_COURSE'])) {
                    $orderItemCreate['tutor_id'] = $metadata['author_id'] ?? null;
                }

                if (in_array($item->item_type, ['SCHOOL_COURSE', 'SCHOOL_CLASS'])) {
                    $orderItemCreate['school_id'] = $metadata['school_id'] ?? null;
                }

                $order->items()->create($orderItemCreate);
            }

            DB::commit();

            // Delete user cart
            
            $cart->delete();
            return jsonResponse(true, 'Order created successfully',[
                'order_number' => $order->order_number
            ]);           
        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(false, 'Order creation failed'.$e->getMessage());           
        }
    }
}
