<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiAuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $response = Http::asForm()->post(config('app.url') . '/oauth/token', [
            'grant_type' => 'password',
            'client_id' => env('PASSPORT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_CLIENT_SECRET'),
            'username' => $request->email,
            'password' => $request->password,
            'scope' => ''
        ]);

        if ($response->failed()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json($response->json());
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->token();
        $token->revoke();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $response = Http::asForm()->post(config('app.url') . '/oauth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => env('PASSPORT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_CLIENT_SECRET'),
            'refresh_token' => $request->input('refresh_token'),
            'scope' => ''
        ]);

        if ($response->failed()) {
            return response()->json(['message' => 'Invalid refresh token'], 401);
        }

        return response()->json($response->json());
    }
}
