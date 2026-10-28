<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\StaffLoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credential',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        $this->auditLogService->logLogin($user, 'Web/Owner/Admin');

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function staffLogin(StaffLoginRequest $request)
    {
        $credentials = $request->validated();

        $user = User::where('username', $credentials['username'])->first();

        if (!$user || !Hash::check($credentials['pin'], $user->pin_hash)) {
            return response()->json([
                'message' => 'Invalid credential',
            ], 401);
        }

        $token = $user->createToken('staff-token')->plainTextToken;

        $this->auditLogService->logLogin($user, 'POS Staff PIN');

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }
}
