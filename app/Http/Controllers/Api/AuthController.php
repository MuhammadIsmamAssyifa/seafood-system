<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = \App\Models\User::where(
            'email',
            $request->email
        )->first();

        if (
            !$user ||
            !Hash::check(
                $request->password,
                $user->password
            )
        ) {
            return response()->json([
                'message' => 'Invalid credential'
            ], 401);
        }
        $token = $user
            ->createToken('api-token')
            ->plainTextToken;

        return response()->json([
            'token' => $token,

            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'roles' => $user->getRoleNames()
            ]
        ]);
    }

    public function staffLogin(Request $request)
    {

        $request->validate([
            'username' => 'required',
            'pin' => 'required'
        ]);


        $user = \App\Models\User::where(
            'username',
            $request->username
        )->first();


        if (
            !$user ||
            !Hash::check(
                $request->pin,
                $user->pin_hash
            )
        ) {

            return response()->json([
                'message' => 'Invalid credential'
            ], 401);
        }


        $token = $user
            ->createToken('staff-token')
            ->plainTextToken;


        return response()->json([

            'token' => $token,

            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'roles' => $user->getRoleNames()
            ]

        ]);
    }
}
