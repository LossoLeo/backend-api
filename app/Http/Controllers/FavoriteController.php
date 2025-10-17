<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFavoriteRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\FavoriteUserResource;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct(
        private FavoriteService $favoriteService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $perPage = $request->query('per_page', 10);

        $favorites = $this->favoriteService->getUserFavorites($user->id, $perPage);

        if (!$favorites) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        return response()->json([
            'data' => ProductResource::collection($favorites->items()),
            'pagination' => [
                'current_page' => $favorites->currentPage(),
                'per_page' => $favorites->perPage(),
                'total' => $favorites->total(),
                'last_page' => $favorites->lastPage(),
                'from' => $favorites->firstItem(),
                'to' => $favorites->lastItem(),
            ]
        ]);
    }

    public function indexAll(Request $request): JsonResponse
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        $perPage = $request->query('per_page', 10);
        $users = $this->favoriteService->getAllUsersWithFavorites($perPage);

        return response()->json([
            'data' => FavoriteUserResource::collection($users->items()),
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

    public function show(int $userId): JsonResponse
    {
        $authUser = auth()->user();

        if (!$authUser->hasRole('admin') && $authUser->id != $userId) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        $user = $this->favoriteService->getUserWithFavorites($userId);

        if (!$user) {
            return response()->json([
                'message' => trans('favorites.user_not_found', [], 'pt_BR')
            ], 404);
        }

        return response()->json(new FavoriteUserResource($user));
    }

    public function store(StoreFavoriteRequest $request): JsonResponse
    {
        $user = auth()->user();

        $result = $this->favoriteService->addFavorite($user, $request->product_id);

        if (!$result) {
            return response()->json([
                'message' => trans('favorites.not_found', [], 'pt_BR')
            ], 404);
        }

        return response()->json([
            'message' => trans('favorites.added', [], 'pt_BR'),
            'product' => $result['product'],
        ], 201);
    }

    public function destroy(int $productId): JsonResponse
    {
        $user = auth()->user();

        $removed = $this->favoriteService->removeFavorite($user, $productId);

        if (!$removed) {
            return response()->json([
                'message' => trans('favorites.not_found', [], 'pt_BR')
            ], 404);
        }

        return response()->json([
            'message' => trans('favorites.removed', [], 'pt_BR')
        ]);
    }
}
