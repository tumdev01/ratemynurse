<?php

namespace App\Traits;

use App\Models\Favorite;
use App\Models\User;

trait Favoritable
{
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'profile');
    }

    public function isFavoritedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->favorites()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function toggleFavorite(User $user): bool
    {
        $favorite = $this->favorites()
            ->where('user_id', $user->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return false;
        }

        $this->favorites()->create([
            'user_id' => $user->id,
        ]);

        return true;
    }
}
