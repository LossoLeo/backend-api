<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        $users = User::with('roles')->get();

        return response()->json($users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->pluck('name')->first(),
                'created_at' => $user->created_at,
            ];
        }));
    }

    public function show(int $id): JsonResponse
    {
        $authUser = auth()->user();

        if (!$authUser->hasRole('admin') && $authUser->id != $id) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        $user = User::with('roles')->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->pluck('name')->first(),
            'created_at' => $user->created_at,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $authUser = auth()->user();

        if (!$authUser->hasRole('admin') && $authUser->id != $id) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        $user = User::find($id);

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

        $validated = $validator->validated();

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Usuário atualizado com sucesso',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->pluck('name')->first(),
            ]
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Usuário removido com sucesso']);
    }
}
