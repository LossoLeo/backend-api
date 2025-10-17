<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        $perPage = $request->query('per_page', 10);
        $users = $this->userService->getAllUsers($perPage);

        return response()->json([
            'data' => UserResource::collection($users->items()),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ]
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $authUser = auth()->user();

        if (!$authUser->hasRole('admin') && $authUser->id != $id) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        return response()->json(new UserResource($user));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $authUser = auth()->user();

        if (!$authUser->hasRole('admin') && $authUser->id != $id) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($id)
            ],
            'password' => 'sometimes|nullable|string|min:6',
        ], [
            'email.unique' => trans('auth.email_already_exists', [], 'pt_BR'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        $updatedUser = $this->userService->updateUser($user, $validator->validated());

        return response()->json([
            'message' => 'Usuário atualizado com sucesso',
            'user' => new UserResource($updatedUser),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        $this->userService->deleteUser($user);

        return response()->json(['message' => 'Usuário removido com sucesso']);
    }
}
