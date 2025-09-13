<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo(User::class);
    }
}