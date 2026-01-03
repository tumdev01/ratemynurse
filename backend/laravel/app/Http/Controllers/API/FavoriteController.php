<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\FavoriteSetRequest;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(FavoriteSetRequest $request)
    {
        $user = $request->user();

        $profileClass = $request->profile_type;
        $profile = $profileClass::findOrFail($request->profile_id);

        $isFavorite = $profile->toggleFavorite($user);

        return response()->json([
            'is_favorite' => $isFavorite,
        ]);
    }

    public function whoFavoritedMe(Request $request)
    {
        $user = $request->user(); // nursing / nursing_home

        // ดึง profile (กรณี hasOne)
        $profile = $user->profile;

        if (! $profile) {
            return response()->json([], 200);
        }

        $favorites = $profile
            ->favoritedUsers()   // ← มาจาก Trait
            ->paginate(20);

        return response()->json($favorites);
    }
}