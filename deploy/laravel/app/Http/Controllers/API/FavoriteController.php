<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\AddFavoriteRequest;
use App\Http\Requests\RemoveFavoriteRequest;
use App\Services\Favorites\FavoriteService;
use App\Services\Favorites\ActionFavoritesService;
use Illuminate\Validation\Rule;

class FavoriteController extends Controller
{
    public function __construct(
        protected ActionFavoritesService $favoriteService,
        protected FavoriteService $service
    ) {}

    /*
    * Request params [user_id, profile_type, profile_id]
    */
    public function add(AddFavoriteRequest $request): JsonResponse
    {
        $user = $request->user();

        $favorite = $this->favoriteService->addFavorite(
            user_id: $user->id,
            profile_type: $request->input('profile_type'),
            profile_id: $request->input('profile_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Favorte added successfully',
            'data' => [
                'id' => $favorite->id,
            ],
        ], 201);
    }

    public function remove(RemoveFavoriteRequest $request): JsonResponse
    {
        $user = $request->user();

        $this->favoriteService->removeFavorite(
            userId: $user->id,
            profileType: $request->input('profile_type'),
            profileId: $request->input('profile_id'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Favorite removed successfully',
        ], 200);
    }

    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'profile_type' => ['required', Rule::in(['NURSING', 'NURSING_HOME'])],
            'profile_id'   => ['required', 'integer'],
        ]);

        $user = $request->user();

        $model = match ($request->profile_type) {
            'NURSING'      => \App\Models\NursingProfile::class,
            'NURSING_HOME' => \App\Models\NursingHomeProfile::class,
        };

        // Ensure profile exists
        $model::findOrFail($request->profile_id);

        $existing = \App\Models\Favorite::where('user_id', $user->id)
            ->where('profile_type', $model)
            ->where('profile_id', $request->profile_id)
            ->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
        } else {
            \App\Models\Favorite::create([
                'user_id'      => $user->id,
                'profile_type' => $model,
                'profile_id'   => $request->profile_id,
            ]);
            $favorited = true;
        }

        return response()->json([
            'success'    => true,
            'favorited'  => $favorited,
            'message'    => $favorited
                ? 'Favorite added successfully'
                : 'Favorite removed successfully',
        ]);
    }

    public function myFavorites(Request $request): JsonResponse
    {
        $user = $request->user();

        $favorites = Favorite::with('profile')
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $favorites,
        ]);
    }

    public function getFavoriteIds(Request $request): JsonResponse
    {
        $request->validate([
            'profile_type' => ['required', Rule::in(['NURSING', 'NURSING_HOME'])],
        ]);

        $user = $request->user();

        $profileClass = match ($request->profile_type) {
            'NURSING'      => \App\Models\NursingProfile::class,
            'NURSING_HOME' => \App\Models\NursingHomeProfile::class,
        };

        $ids = \App\Models\Favorite::where('user_id', $user->id)
            ->where('profile_type', $profileClass)
            ->pluck('profile_id');

        return response()->json([
            'success' => true,
            'data' => $ids,
        ]);
    }

    public function getFavoritesPaginate(Request $request): JsonResponse
    {
        $request->validate([
            'profile_type' => ['nullable', Rule::in(['NURSING', 'NURSING_HOME'])],
            'key' => ['nullable', 'string'],
        ]);

        $favorites = $this->service->getFavoritesPaginate(
            user: $request->user(),
            profileType: $request->profile_type,
            key: $request->key,
        );

        return response()->json([
            'success' => true,
            'data' => $favorites,
        ]);
    }

    public function removeAsProvider(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $profileClass = match ($user->user_type) {
            'NURSING'      => \App\Models\NursingProfile::class,
            'NURSING_HOME' => \App\Models\NursingHomeProfile::class,
            default        => null,
        };

        if (!$profileClass) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid provider role',
            ], 403);
        }

        $favorite = \App\Models\Favorite::with('profile')->find($id);

        if (!$favorite || $favorite->profile_type !== $profileClass || !$favorite->profile) {
            return response()->json([
                'success' => false,
                'message' => 'Favorite not found',
            ], 404);
        }

        if ($favorite->profile->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Favorite removed successfully',
        ]);
    }

    public function getFavoritesForProviderPaginate(Request $request): JsonResponse
    {
        $request->validate([
            'profile_type' => ['required', Rule::in(['NURSING', 'NURSING_HOME'])],
            'key'          => ['nullable', 'string'],
        ]);

        $favorites = $this->service->getFavoritesForProviderPaginate(
            user: $request->user(),
            profileType: $request->profile_type,
            key: $request->key
        );

        return response()->json([
            'success' => true,
            'data' => $favorites,
        ]);
    }
}
