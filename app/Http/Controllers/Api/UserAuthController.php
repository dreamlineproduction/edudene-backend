<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    //
    public function register(Request $request)
    {
        $request->validate([
            'role_id' => 'required|integer',
            'full_name' => 'required|string|max:150',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:5',
            'timezone' => 'required|string|max:150',           
        ]);

        $user = User::create($request->toArray());

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 200,
            'message' => 'User registered successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }



    // 🔑 Login API
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found in our database.',
            ]);          
        }

        if($user->status === 'Inactive'){
            return response()->json([
                'status' => 403,
                'message' => 'Your account is inactive. Please active your account.',
            ],403);
        }

        if(!Hash::check($request->password, $user->password)){
            return response()->json([
                'status' => 401,
                'message' => 'Invalid credentials.',
            ],401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $user,
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Logout successful.',
        ]);
    }
}
