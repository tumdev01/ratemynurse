<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\FavoriteSetRequest;
use App\Models\Favorite;
use App\Models\NursingProfile;
use App\Models\NursingHomeProfile;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    // frontend ส่ง profile_type เป็น 'NURSING'/'NURSING_HOME' (เหมือน user_type ทั่วระบบ) — map เป็น
    // model class ภายในเอง แทนให้ client ต้องรู้ namespace จริงของ backend
    private function resolveProfileClass(string $profileType): string
    {
        return match (strtoupper($profileType)) {
            'NURSING' => NursingProfile::class,
            'NURSING_HOME' => NursingHomeProfile::class,
            default => abort(422, 'Invalid profile_type'),
        };
    }

    public function toggle(FavoriteSetRequest $request)
    {
        $user = $request->user();
        $profileClass = $this->resolveProfileClass($request->profile_type);
        $profile = $profileClass::findOrFail($request->profile_id);

        $isFavorite = $profile->toggleFavorite($user);

        return response()->json([
            'is_favorite' => $isFavorite,
        ]);
    }

    public function getFavoriteIds(Request $request)
    {
        $user = $request->user();
        $profileClass = $this->resolveProfileClass((string) $request->query('profile_type', ''));

        $ids = Favorite::where('user_id', $user->id)
            ->where('profile_type', $profileClass)
            ->pluck('profile_id');

        return response()->json(['data' => $ids]);
    }

    public function getFavoritesPaginate(Request $request)
    {
        $user = $request->user();
        $profileClass = $this->resolveProfileClass((string) $request->query('profile_type', 'NURSING'));
        $key = trim((string) $request->query('key', ''));

        $query = Favorite::with(['profile' => function ($morphTo) {
            $morphTo->morphWith([
                NursingProfile::class => ['owner', 'coverImage'],
                NursingHomeProfile::class => ['coverImage'],
            ]);
        }])
            ->where('user_id', $user->id)
            ->where('profile_type', $profileClass)
            ->latest();

        if ($key !== '') {
            $matchingIds = $profileClass::where('name', 'like', "%{$key}%")->pluck('id');
            $query->whereIn('profile_id', $matchingIds);
        }

        return response()->json($query->paginate(12));
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

    // ให้ provider (Nursing/NursingHome) ลบตัวเองออกจากรายการโปรดของสมาชิกที่เคยกดไว้
    public function removeAsProvider(Request $request, int $id)
    {
        $user = $request->user();
        $favorite = Favorite::findOrFail($id);
        $profile = $favorite->profile;

        if (!$profile || $profile->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $favorite->delete();

        return response()->json(['success' => true]);
    }
}
