<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\User;
use App\Models\CartItem;
use App\Models\CourseAsset;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;

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
            return jsonResponse(true, 'fetch cart data', [
                'items' => [],
                'total' => 0
            ]);
        }

        $items = $cart->items()->with('item')->get();
        

        $data = $items->map(function ($row) {
            $image = Null;

            if($row->item_type === 'SCHOOL_CLASS' && !empty($row->item->cover_image)) {
                if(!empty($row->item->cover_image)) {
                    $image = $row->item->cover_image_url;    
                }                
            }

            if($row->item_type === 'COURSE') {
                $courseAsset = CourseAsset::where('course_id',$row->item_id)->first(); 

                if(!empty($courseAsset) && !empty($courseAsset->poster)){
                     $image = $courseAsset->poster_url;
                }
            }

            $tutor = User::where('id',$row->item->tutor_id)->first();

            return [
                'id'        => $row->id,
                'title'     => $row->title,
                'price'     => $row->price,
                'discount_price'     => $row->discount_price,
                'qty'       => $row->qty,
                'total'     => $row->discount_price * $row->qty,
                'model'     => $row->model_name,
                'meta_data' => $row->metadata,
                'image'     => $image,
                'tutor'     => $tutor,
                //'row' => $row
            ];
        });

        return jsonResponse(true, 'fetch cart data', [
            'cart_id' => $cart->id,
            'items'   => $data,
            'total'   => $data->sum('total')
        ]);
    }



    public function add(Request $request)
    {
        $request->validate([
            'item_type' => 'required|string',
            'item_id'   => 'required|integer',
            'title'     => 'required|string',
            'price'     => 'required|numeric|min:0',
            'discount_price'     => 'required|numeric|min:0',
            'qty'       => 'nullable|integer|min:1',
            'metadata'  => 'nullable|array'
        ]);

        $cart = $this->getCart($request);

        if (!$cart) {
            return jsonResponse(false,'Cart token missing',null,400);
        }

        $find =  [
            'cart_id'   => $cart->id,
            'item_type' => $request->item_type,
            'item_id'   => $request->item_id,
        ];

        $count = CartItem::where($find)->count();

        if($count > 0) {
            return jsonResponse(false,'This item is already in your cart',null,400);
        }

        $modelName = null;
        $message = 'Item added to cart';

        if($request->item_type === 'SCHOOL_CLASS'){
            $modelName = 'App\Models\Classes';
            $message  = "Class added to cart successfully.";
        }

        if($request->item_type === "COURSE"){
            $modelName = 'App\Models\Course';
            $message = "Course added to cart successfully.";
        }

        $item = CartItem::updateOrCreate($find,[
                'title'    => $request->title,
                'price'    => $request->price,
                'discount_price'    => $request->discount_price,
                //'qty'      => DB::raw('qty + ' . ($request->qty ?? 1)),
                'qty'      => 1,
                'model_name' => $modelName,
                'metadata' => $request->metadata
            ]
        );

        return response()->json([
            'message' => $message,
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


    public function mergeAfterLogin(Request $request,$userId = 0)
    {

        $token = $request->cookie('cart_token');
        //$user  = auth('sanctum')->user();

        //print_r($user);

        if (!$token || $userId === 0) {
            return;
        }

        echo 'He12';
        $guestCart = Cart::with('items')
            ->where('cart_token', $token)
            ->whereNull('user_id')
            ->first();

        if (!$guestCart) return;

        $userCart = Cart::firstOrCreate(
            ['user_id' => $userId],
            ['cart_token' => null]
        );

        foreach ($guestCart->items as $item) {

            $existing = CartItem::where('cart_id', $userCart->id)
                ->where('item_type', $item->item_type)
                ->where('item_id', $item->item_id)
                ->first();

            if ($existing) {
                $existing->qty += $item->qty;
                $existing->save();
            } else {
                CartItem::create([
                    'cart_id'   => $userCart->id,
                    'item_type' => $item->item_type,
                    'item_id'   => $item->item_id,
                    'title'     => $item->title,
                    'price'     => $item->price,
                    'qty'       => $item->qty,
                    'metadata'  => $item->metadata,
                ]);
            }
        }

        $guestCart->delete();

        Cookie::queue(Cookie::forget('cart_token'));
    }
}
