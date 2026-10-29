<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\StaffLoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credential',
            ], 401);
        }

        $deviceName = $credentials['device_name'] ?? 'web';
        $token = $user->createToken($deviceName)->plainTextToken;

        $this->auditLogService->logLogin($user, 'Web/Owner/Admin');

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function staffLogin(StaffLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::where('username', $credentials['username'])->first();

        if (! $user || ! Hash::check($credentials['pin'], $user->pin_hash)) {
            return response()->json([
                'message' => 'Invalid credential',
            ], 401);
        }

        $deviceName = $credentials['device_name'] ?? 'pos';
        $token = $user->createToken($deviceName)->plainTextToken;

        $this->auditLogService->logLogin($user, 'POS Staff PIN');

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'All device tokens revoked successfully',
        ]);
    }
}
