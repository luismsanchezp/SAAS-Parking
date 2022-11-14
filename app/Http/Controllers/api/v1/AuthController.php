<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Requests\api\v1\AuthSignInRequest;

class AuthController extends Controller
{
    public function login(AuthSignInRequest $request)
    {
        $credentials = $request->only('email', 'password');
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'error' => 'Invalid login details'
            ], 401);
        }
        $tokenType = 'Bearer';
        $user = User::where('email', $request['email'])->firstOrFail();
        $user->tokens()->where('name', $tokenType)->delete();
        $token = $user->createToken($tokenType);
        return response()->json([
            'token' => $token->plainTextToken,
            'type' => $tokenType
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Token revoked'
        ], 200);
    }
}
