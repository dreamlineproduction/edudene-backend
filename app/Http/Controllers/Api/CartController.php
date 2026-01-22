<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    //
    /**
     * Get or create cart
     */
    private function getCart(Request $request)
    {
        $token = $request->cookie('cart_token');

        if (!$token) {
            return null;
        }

        return Cart::firstOrCreate(
            ['cart_token' => $token],
            ['user_id' => Auth::id()]
        );
    }


    public function index(Request $request)
    {
        $cart = $this->getCart($request);

        if (!$cart) {
            return response()->json([
                'items' => [],
                'total' => 0
            ]);
        }

        $items = $cart->items()->get();

        $total = $items->sum(function ($item) {
            return $item->price * $item->qty;
        });

        return response()->json([
            'cart_id' => $cart->id,
            'items'   => $items,
            'total'   => $total
        ]);
    }



    public function add(Request $request)
    {
        $request->validate([
            'item_type' => 'required|string',
            'item_id'   => 'required|integer',
            'title'     => 'required|string',
            'price'     => 'required|numeric|min:0',
            'qty'       => 'nullable|integer|min:1',
            'metadata'  => 'nullable|array'
        ]);

        $cart = $this->getCart($request);

        if (!$cart) {
            return response()->json([
                'message' => 'Cart token missing'
            ], 400);
        }

        $item = CartItem::updateOrCreate(
            [
                'cart_id'   => $cart->id,
                'item_type' => $request->item_type,
                'item_id'   => $request->item_id,
            ],
            [
                'title'    => $request->title,
                'price'    => $request->price,
                'qty'      => DB::raw('qty + ' . ($request->qty ?? 1)),
                'metadata' => $request->metadata
            ]
        );

        return response()->json([
            'message' => 'Item added to cart',
            'item'    => $item
        ]);
    }

    public function updateQty(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer',
            'qty'     => 'required|integer|min:1'
        ]);

        $cart = $this->getCart($request);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('id', $request->item_id)
            ->firstOrFail();

        $item->update([
            'qty' => $request->qty
        ]);

        return response()->json([
            'message' => 'Quantity updated'
        ]);
    }


    public function remove(Request $request, $id)
    {
        $cart = $this->getCart($request);

        CartItem::where('cart_id', $cart->id)
            ->where('id', $id)
            ->delete();

        return response()->json([
            'message' => 'Item removed'
        ]);
    }


   
    public function clear(Request $request)
    {
        $cart = $this->getCart($request);

        $cart->items()->delete();

        return response()->json([
            'message' => 'Cart cleared'
        ]);
    }


    public function mergeAfterLogin(Request $request)
    {
        $token = $request->cookie('cart_token');

        if (!$token || !Auth::check()) {
            return;
        }

        $guestCart = Cart::where('cart_token', $token)
            ->whereNull('user_id')
            ->first();

        if (!$guestCart) return;

        $userCart = Cart::firstOrCreate([
            'user_id' => Auth::id()
        ], [
            'cart_token' => $token
        ]);

        foreach ($guestCart->items as $item) {

            CartItem::updateOrCreate(
                [
                    'cart_id'   => $userCart->id,
                    'item_type' => $item->item_type,
                    'item_id'   => $item->item_id,
                ],
                [
                    'title'    => $item->title,
                    'price'    => $item->price,
                    'qty'      => DB::raw('qty + ' . $item->qty),
                    'metadata' => $item->metadata
                ]
            );
        }

        $guestCart->delete();
    }
}
