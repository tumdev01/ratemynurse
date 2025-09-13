<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\NursingHomeRateType;

class RateDetail extends Model
{
    protected $table = 'rate_details';
    protected $appends = ['scores_for_label'];

    protected $fillable = [
        'rate_id',
        'scores',
        'scores_for'
    ];

    public function getScoresForLabelAttribute()
    {
        $map = NursingHomeRateType::list();
        return $map[$this->scores_for] ?? $this->scores_for;
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(Rate::class);
    }
}