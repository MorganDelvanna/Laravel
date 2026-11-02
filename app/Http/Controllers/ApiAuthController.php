<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Support\LegacyPassword;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Log;

class ApiAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('UserName', $credentials['username'])->first();
        Log::debug('API user lookup', [
            'found' => $user !== null,
            'id' => $user?->id,
            'UserName' => $user?->UserName,
            'Role' => $user?->role,
        ]);

        if (!$user || !LegacyPassword::check($credentials['password'], $user->getAuthPassword())) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if (!$user instanceof User || $user->role !== 'admin') {
            return response()->json(['message' => 'This account is not authorized to access the API.'], 403);
        }

        $token = $user->createToken(
            'desktop-app',
            ['applicants:read'],
            now()->addDays(30)
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->accessToken->expires_at,
            'user' => [
                'id' => $user->id,
                'username' => $user->UserName,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if (!$token instanceof PersonalAccessToken) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $token->delete();

        return response()->json(['message' => 'Logged out.']);
    }

}
