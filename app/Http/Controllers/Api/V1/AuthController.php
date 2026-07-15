<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return ApiResponse::unauthorized('Invalid credentials');
        }

        $token = $user->createToken('auth-token', [$user->role])->plainTextToken;
        $refreshToken = Str::random(80);

        return ApiResponse::success([
            'token' => $token,
            'refresh_token' => $refreshToken,
            'user' => $this->formatUser($user),
        ], 'Login successful');
    }

    public function refresh(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $user = User::where('remember_token', $validated['refresh_token'])->first();

        if (!$user) {
            return ApiResponse::unauthorized('Invalid refresh token');
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth-token', [$user->role])->plainTextToken;
        $refreshToken = Str::random(80);
        $user->forceFill(['remember_token' => $refreshToken])->save();

        return ApiResponse::success([
            'token' => $token,
            'refresh_token' => $refreshToken,
            'user' => $this->formatUser($user),
        ], 'Token refreshed');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->formatUser($request->user())
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logged out successfully');
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'email' => $user->email,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'role' => $user->role,
            'tenantId' => $user->tenant_id,
            'phone' => $user->phone,
            'avatarUrl' => $user->avatar_url,
            'language' => $user->language,
            'theme' => $user->theme,
            'institution' => $user->institution,
            'department' => $user->department,
            'isActive' => $user->is_active,
        ];
    }
}
