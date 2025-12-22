<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid email or password'
            ], 401);
        }

        $user = Auth::user()->load('role', 'company');

        // Block inactive users
        if (!$user->is_active) {
            return response()->json([
                'message' => 'Your account is inactive'
            ], 403);
        }

        // Create Sanctum token
        $token = $user->createToken('attendance-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'role'          => $user->role->slug,
                'company_id'    => $user->company_id,
                'company_name'  => $user->company?->name,
            ]
        ]);
    }

     /**
     * Logout API
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    // get roles api

    public function getRoles(){

        $roles = Role::all(['id','name','slug']);
        return response()->json([
            'roles' => $roles
        ]);

    }

    // get subscription plans api
    public function getSubscriptionPlans(){
        $subscriptions = Subscription::with('features')->where('is_active',1)->get();
        return response()->json([
            'subscriptions' => $subscriptions
        ]);
    }

}
