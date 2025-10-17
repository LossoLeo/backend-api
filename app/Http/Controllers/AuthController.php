<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\AuthResource;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'message' => trans('auth.register_success', [], 'pt_BR'),
            'user' => new AuthResource($user),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->email, $request->password);

        if (!$result) {
            return response()->json([
                'message' => trans('auth.failed', [], 'pt_BR')
            ], 401);
        }

        return response()->json([
            'message' => trans('auth.login_success', [], 'pt_BR'),
            'user' => (new AuthResource($result['user']))->withToken($result['token']),
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout(auth()->user());

        return response()->json([
            'message' => trans('auth.logout_success', [], 'pt_BR')
        ]);
    }

    public function me(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->pluck('name')->first(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }
}
