<?php

namespace App\Services\Favorites;

use App\Models\Favorite;
use App\Repositories\Favorites\FavoriteRepository;
class ActionFavoritesService
{
    protected string $tz = 'Asis/Bangkok';

    public function __construct(
        protected FavoriteRepository $repository
    ) {}

    public function addFavorite(
        int $user_id,
        string $profile_type,
        int $profile_id
    ): Favorite {
        return $this->repository->addAction([
            'user_id' => $user_id,
            'profile_type' => $profile_type,
            'profile_id' => $profile_id
        ]);
    }

    public function removeFavorite(
        int $userId,
        string $profileType,
        int $profileId
    ): void {
        $this->repository->removeAction(
            userId: $userId,
            profileType: $profileType,
            profileId: $profileId
        );
    }
}