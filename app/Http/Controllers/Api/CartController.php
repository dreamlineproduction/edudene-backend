<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\User;
use App\Models\CartItem;
use App\Models\CourseAsset;
use App\Models\CourseChapter;
use App\Models\Tutor;
use App\Models\CourseBulkDiscount;

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
        if (auth('sanctum')->check()) {
            return Cart::firstOrCreate(
                ['user_id' => auth('sanctum')->id()],
                ['cart_token' => null]
            );
        }

        $token = $request->cookie('cart_token');

        if (!$token) {
            $token = bin2hex(random_bytes(16));
            Cookie::queue('cart_token', $token, 60 * 24 * 7);
        }

        return Cart::firstOrCreate(['cart_token' => $token]);
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

            $image = null;
            $images = [];

            /*
            |--------------------------------------------------------------------------
            | GROUP ITEMS
            |--------------------------------------------------------------------------
            */
            if ($row->is_group && !empty($row->metadata)) {

                $images = collect($row->metadata)->map(function ($item) {

                    $img = null;

                    // Course Image
                    if (in_array($item['item_type'], ['SCHOOL_COURSE', 'TUTOR_COURSE'])) {
                        $courseAsset = CourseAsset::where('course_id', $item['item_id'])->first();

                        if (!empty($courseAsset?->poster)) {
                            $img = $courseAsset->poster_url;
                        }
                    }

                    // Slot Image (Tutor)
                    if (in_array($item['item_type'], ['SCHOOL_SLOT', 'TUTOR_SLOT'])) {
                        $tutor = Tutor::where('user_id', $item['metadata']['author_id'] ?? null)->first();

                        if (!empty($tutor?->avatar_url)) {
                            $img = $tutor->avatar_url;
                        }
                    }

                    return $img;

                })->filter()->values()->toArray();

                //  first image as thumbnail
                $image = $images[0] ?? null;
            }

            /*
            |--------------------------------------------------------------------------
            | SINGLE ITEMS
            |--------------------------------------------------------------------------
            */
            if (!$row->is_group) {

                // Class Image
                if ($row->item_type === 'SCHOOL_CLASS' && !empty($row->item?->cover_image)) {
                    $image = $row->item->cover_image_url;
                }

                // Course Image
                if (in_array($row->item_type, ['SCHOOL_COURSE', 'TUTOR_COURSE'])) {
                    $courseAsset = CourseAsset::where('course_id', $row->item_id)->first();

                    if (!empty($courseAsset?->poster)) {
                        $image = $courseAsset->poster_url;
                    }
                }

                // Chapter Image
                if (in_array($row->item_type, ['SCHOOL_COURSE_CHAPTER', 'COURSE_CHAPTER'])) {
                    $courseAsset = CourseAsset::where('course_id', $row->metadata['course_id'] ?? null)->first();

                    if (!empty($courseAsset?->poster)) {
                        $image = $courseAsset->poster_url;
                    }
                }

                // Slot Image
                if (in_array($row->item_type, ['SCHOOL_SLOT', 'TUTOR_SLOT'])) {
                    $tutor = Tutor::where('user_id', $row->metadata['author_id'] ?? null)->first();

                    if (!empty($tutor?->avatar_url)) {
                        $image = $tutor->avatar_url;
                    }
                }
            }

            return [
                'id'        => $row->id,
                'item_type' => $row->item_type,
                'item_id'   => $row->item_id,
                'is_group'  => $row->is_group,
                'title'     => $row->title,
                'slug'     => $row->slug,
                'price'     => $row->price,
                'discount_price' => $row->discount_price,
                'qty'       => $row->qty,
                'total'     => $row->discount_price * $row->qty,
                'model'     => $row->model_name,
                'meta_data' => $row->metadata,

                // important
                'image'     => $image,     // thumbnail
                'images'    => $images,    // group images (optional frontend use)
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
            'is_group' => 'required|boolean',
            'cartItem' => 'nullable|array',
            'cartGroupItem' => 'nullable|array',
        ]);

        $cart = $this->getCart($request);
        if (!$cart) {
            return jsonResponse(false, 'Cart token missing', null, 400);
        }

        if ($request->is_group) {
            return $this->handleGroupItem($request->cartGroupItem, $cart);
        }

        return $this->handleSingleItem($request->cartItem, $cart);
    }

    private function handleGroupItem($groupItemData, $cart)
    {
        validator($groupItemData, [
            'item_type' => 'required|string',
            'item_id' => 'required|integer',
            'title' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'required|numeric|min:0',
            'metadata.products' => 'required|array|min:1',
        ])->validate();

        // Duplicate check
        $exists = CartItem::where([
            'cart_id' => $cart->id,
            'item_type' => $groupItemData['item_type'],
            'item_id' => $groupItemData['item_id'],
        ])->exists();

        if ($exists) {
            return jsonResponse(false, 'This package is already in your cart', null, 400);
        }

        // Min quantity check
        $package = CourseBulkDiscount::find($groupItemData['item_id']);

        if ($package && count($groupItemData['metadata']['products']) < $package->min_quantity) {
            return jsonResponse(false, "Minimum {$package->min_quantity} courses required", null, 400);
        }

        $item = CartItem::create([
            'cart_id' => $cart->id,
            'item_type' => $groupItemData['item_type'],
            'item_id' => $groupItemData['item_id'],
            'title' => $groupItemData['title'],
            'price' => $groupItemData['price'],
            'discount_price' => $groupItemData['discount_price'],
            'qty' => 1,
            'model_name' => null,
            'slug' => null,
            'metadata' => $groupItemData['metadata']['products'],
            'is_group' => true,
        ]);

        return response()->json([
            'message' => 'Package added to cart successfully',
            'item' => $item
        ]);
    }

    private function handleSingleItem($itemData, $cart)
    {
        validator($itemData, [
            'item_type' => 'required|string',
            'item_id'   => 'required|integer',
            'title'     => 'required|string',
            'price'     => 'required|numeric|min:0',
            'discount_price' => 'required|numeric|min:0',
            'metadata'  => 'nullable|array'
        ])->validate();

        $find = [
            'cart_id'   => $cart->id,
            'item_type' => $itemData['item_type'],
            'item_id'   => $itemData['item_id'],
        ];

        $exists = CartItem::where($find)->exists();

        if ($exists) {
            return jsonResponse(false, 'This item is already in your cart', null, 400);
        }

        // Model resolve
        $modelName = $this->resolveModel($itemData['item_type']);

        $item = CartItem::create([
            'cart_id' => $cart->id,
            'item_type' => $itemData['item_type'],
            'item_id' => $itemData['item_id'],
            'title' => $itemData['title'],
            'slug' => $itemData['slug'],
            'price' => $itemData['price'],
            'discount_price' => $itemData['discount_price'],
            'qty' => 1,
            'model_name' => $modelName,
            'metadata' => $itemData['metadata'] ?? null,
            'is_group' => false,
        ]);

        return response()->json([
            'message' => 'Item added to cart successfully',
            'item' => $item
        ]);
    }

    private function resolveModel($type = null)
    {
        return match ($type) {
            'SCHOOL_CLASS', 'TUTOR_CLASS' => 'App\Models\Classes',
            'SCHOOL_COURSE', 'TUTOR_COURSE' => 'App\Models\Course',
            'SCHOOL_COURSE_CHAPTER', 'TUTOR_COURSE_CHAPTER' => 'App\Models\Course',
            'TUTOR_SLOT', 'SCHOOL_SLOT' => 'App\Models\OneOnOneClassSlot',
            default => null,
        };
    }

    // public function addOLD(Request $request)
    // {
    //     $request->validate([
    //         'item_type' => 'required|string',
    //         'item_id'   => 'required|integer',
    //         'title'     => 'required|string',
    //         'price'     => 'required|numeric|min:0',
    //         'discount_price'     => 'required|numeric|min:0',
    //         'qty'       => 'nullable|integer|min:1',
    //         'metadata'  => 'nullable|array'
    //     ]);

    //     $cart = $this->getCart($request);

    //     if (!$cart) {
    //         return jsonResponse(false,'Cart token missing',null,400);
    //     }

    //     $find =  [
    //         'cart_id'   => $cart->id,
    //         'item_type' => $request->item_type,
    //         'item_id'   => $request->item_id,
    //     ];

    //     $count = CartItem::where($find)->count();

    //     if($count > 0) {
    //         return jsonResponse(false,'This item is already in your cart',null,400);
    //     }

    //     $modelName = null;
    //     $message = 'Item added to cart';

    //     if($request->item_type === 'SCHOOL_CLASS' || $request->item_type === 'TUTOR_CLASS'){
    //         $modelName = 'App\Models\Classes';
    //         $message  = "Class added to cart successfully.";
    //     }

    //     if($request->item_type === "SCHOOL_COURSE" || $request->item_type === "TUTOR_COURSE"){
    //         $modelName = 'App\Models\Course';
    //         $message = "Course added to cart successfully.";
    //     }

    //     if($request->item_type === "SCHOOL_COURSE_CHAPTER" || $request->item_type === "TUTOR_COURSE_CHAPTER"){
    //         $modelName = 'App\Models\Course';
    //         $message = "Course chapter added to cart successfully.";
    //     }

    //     if($request->item_type === "TUTOR_SLOT" || $request->item_type === "SCHOOL_SLOT"){
    //         $modelName = 'App\Models\OneOnOneClassSlot';
    //         $message = "Slot added to cart successfully.";
    //     }


    //     $item = CartItem::updateOrCreate($find,[
    //             'title'    => $request->title,
    //             'price'    => $request->price,
    //             'discount_price'    => $request->discount_price,
    //             //'qty'      => DB::raw('qty + ' . ($request->qty ?? 1)),
    //             'qty'      => 1,
    //             'model_name' => $modelName,
    //             'metadata' => $request->metadata
    //         ]
    //     );

    //     return response()->json([
    //         'message' => $message,
    //         'item'    => $item
    //     ]);
    // }

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

    public function removeViaItemId(Request $request, $itemId,$type)
    {
        CartItem::where(['item_id'=>$itemId,'item_type'=>$type])
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


    public function mergeAfterLogin(Request $request, $userId)
    {
        $token = $request->cookie('cart_token');

        if (!$token || !$userId) return;

        $guestCart = Cart::with('items')
            ->where('cart_token', $token)
            ->whereNull('user_id')
            ->first();

        if (!$guestCart) return;

        // Find or create user's cart
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
                $item->cart_id = $userCart->id;
                $item->save();
            }
        }

        // Delete guest cart
        $guestCart->delete();

        Cookie::queue(Cookie::forget('cart_token'));
    }
}
