<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if ($user &&
            Hash::check($request->password, $user->password) &&
            $user->active === 1) {

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token_type' => 'Bearer',
                'access_token' => $token,
            ]);
        } else {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function me(Request $request): mixed
    {
        return $request->user();
    }
}
