<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SubDistrict
 *
 * @package App\Models
 *
 * @property integer id
 * @property string name
 * @property integer district_id
 * @property-read District district
 */
class SubDistrict extends Model
{
    public $timestamps = false;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'district_id' => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|District
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
