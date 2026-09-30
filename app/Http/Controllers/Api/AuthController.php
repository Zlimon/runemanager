<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureUserIsNotSuspended;
use App\Http\Requests\PluginLoginRequest;
use App\Services\Users\CredentialCheck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(PluginLoginRequest $request, CredentialCheck $credentials): JsonResponse
    {
        $user = $credentials->user($request->validated('email'), $request->validated('password'));

        if ($user === null) {
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        if ($user->isSuspended()) {
            return response()->json(['message' => EnsureUserIsNotSuspended::MESSAGE], 403);
        }

        $token = $user->createToken('plugin')->plainTextToken;

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token,
        ]);
    }

    public function me(Request $request): mixed
    {
        return $request->user();
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Token revoked']);
    }
}
