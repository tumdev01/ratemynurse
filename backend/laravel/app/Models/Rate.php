<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rate extends Model
{
    protected $fillable = [
        'text', 'author_id', 'name', 'description', 'rateable_id', 'rateable_type'
    ];

    public function rateable(): MorphTo
    {
        return $this->morphTo();
    }

    public function rate_details(): HasMany
    {
        return $this->hasMany(RateDetail::class, 'rate_id', 'id');
    }
}
