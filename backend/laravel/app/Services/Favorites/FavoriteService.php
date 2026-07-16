<?php

namespace App\Services\Favorites;

use App\Models\User;
use App\Models\NursingProfile;
use App\Models\NursingHomeProfile;
use App\Repositories\Favorites\FavoriteRepository;

class FavoriteService
{
    public function __construct(
        protected FavoriteRepository $repository
    ) {}

    public function getFavoritesPaginate(
        User $user,
        ?string $profileType,
        ?string $key
    ) {
        return $this->repository->paginateByUser(
            userId: $user->id,
            profileType: $profileType,
            key: $key,
        );
    }

    public function getFavoritesForProviderPaginate(
        User $user,
        string $profileType,
        ?string $key
    ) {
        // Query profile
        if ($profileType === 'NURSING') {
            $providerProfile = NursingProfile::where('user_id', $user->id)->first();
        } elseif ($profileType === 'NURSING_HOME') {
            $providerProfile = NursingHomeProfile::where('user_id', $user->id)->first();
        } else {
            throw new \Exception('Invalid profile type');
        }

        if (!$providerProfile) {
            throw new \Exception("No {$profileType} profile found");
        }

        // Query favorites
        return $providerProfile->favorites()
            ->with([
                'user.member.province',
                'user.member.district',
                'user.member.coverImage'
            ])
            ->when($key, function ($q) use ($key) {
                $q->whereHas('user.member', function ($query) use ($key) {
                    $query
                        ->where('name', 'like', "%{$key}%")
                        ->orWhere('email', 'like', "%{$key}%")
                        ->orWhere('phone', 'like', "%{$key}%")
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
