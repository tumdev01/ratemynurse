<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RateDetail extends Model
{
    protected $table = 'rate_details';

    protected $fillable = [
        'rate_id',
        'scores',
        'scores_for'
    ];

    public function rate(): BelongsTo
    {
        return $this->belongsTo(Rate::class);
    }
}