<?php
namespace App\Repositories\Favorites;

use App\Models\Favorite;
use App\Models\NursingProfile;
use App\Models\NursingHomeProfile;
use App\Repositories\BaseRepository;

class FavoriteRepository extends BaseRepository
{
    public function addAction(array $data): Favorite
    {
        $favorite = Favorite::create([
            'user_id' => $data['user_id'],
            'profile_type' => $data['profile_type'],
            'profile_id'   => $data['profile_id'],
            'created_at' => now(),
        ]);

        return $favorite;
    }

    public function removeAction(
        int $userId,
        string $profileType,
        int $profileId
    ): void {
        Favorite::where('user_id', $userId)
            ->where('profile_type', $profileType)
            ->where('profile_id', $profileId)
            ->delete();
    }

    public function findAction(
        string $key
    ): Favorite
    {
        
    }

    public function paginateByUser(
        int $userId,
        ?string $profileType,
        ?string $key
    ) {
        $profileClass = $profileType === 'NURSING'
            ? NursingProfile::class
            : NursingHomeProfile::class;

        $profileTable = (new $profileClass)->getTable();
        return Favorite::query()
            ->where('user_id', $userId)
            ->with([
                'profile' => function ($q) use ($profileClass, $profileTable) {

                    $q->with(['province', 'district'])
                    ->withCount(['rates as review_count'])
                    ->selectSub(function ($sub) use ($profileClass, $profileTable) {
                        $sub->from('rate_details')
                            ->join('rates', 'rate_details.rate_id', '=', 'rates.id')
                            ->selectRaw('ROUND(AVG(rate_details.scores), 1)')
                            ->where('rates.rateable_type', $profileClass)
                            ->whereColumn(
                                'rates.rateable_id',
                                "{$profileTable}.id"
                            );
                    }, 'average_score');
                },

                'profile.coverImage',
                'profile.owner:id,phone,email',
            ])
            ->when($profileType, function ($q) use ($profileType) {
                $q->where('profile_type', $this->mapProfileType($profileType));
            })
            ->when($key, function ($q) use ($key) {
                $q->whereHasMorph(
                    'profile',
                    [NursingProfile::class, NursingHomeProfile::class],
                    function ($query) use ($key) {
                        $query
                            ->where('name', 'like', "%{$key}%")
                            ->orWhere('description', 'like', "%{$key}%")
                            ->orWhereHas('province', fn ($q) =>
                                $q->where('name', 'like', "%{$key}%")
                            )
                            ->orWhereHas('district', fn ($q) =>
                                $q->where('name', 'like', "%{$key}%")
                            );
                    }
                );
            })
            ->latest()
            ->paginate(10);

    }

    private function mapProfileType(string $type): string
    {
        return match ($type) {
            'NURSING' => NursingProfile::class,
            'NURSING_HOME' => NursingHomeProfile::class,
        };
    }

// Repository - แก้ไข query logic
public function paginateForProvider(
    string $profileType, // นี่คือ morph class เช่น App\Models\NursingProfile
    int $profileId,      // นี่คือ nursing_profile.id
    ?string $key
) {

    return Favorite::query()
        ->where('profile_type', $profileType)  // morph type
        ->where('profile_id', $profileId)       // morph id
        ->with([
            // MEMBER ที่มากดถูกใจ
            'user.memberProfile.province',
            'user.memberProfile.district',
        ])
        ->when($key, function ($q) use ($key) {
            $q->whereHas('user.memberProfile', function ($query) use ($key) {
                $query
                    ->where('name', 'like', "%{$key}%")
                    ->orWhere('description', 'like', "%{$key}%")
                    ->orWhereHas('province', fn ($q) =>
                        $q->where('name', 'like', "%{$key}%")
                    )
                    ->orWhereHas('district', fn ($q) =>
                        $q->where('name', 'like', "%{$key}%")
                    );
            });
        })
        ->latest()
        ->paginate(10);
}

}