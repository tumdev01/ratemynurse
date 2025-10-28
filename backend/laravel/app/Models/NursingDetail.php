<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NursingDetail extends Model {
    protected $table = 'nursing_details';

    protected $fillable = [
        'user_id',
        'about',
        'hire_rules',
        'skills',
        'other_skills'
    ];

    /**
     * Get all of the comments for the NursingDetail
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images()
    {
        return $this->hasMany(NursingDetailImage::class, 'detail_id', 'id');
    }
}