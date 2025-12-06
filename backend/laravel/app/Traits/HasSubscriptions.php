<?php

namespace App\Traits;

use App\Models\UserSubscription;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasSubscriptions
{
    public function subscriptions()
    {
        return $this->morphMany(UserSubscription::class, 'subscribable');
    }

    public function subscription()
    {
        return $this->morphOne(UserSubscription::class, 'subscribable')->latestOfMany();
    }
}
