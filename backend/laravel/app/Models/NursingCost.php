<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NursingCost extends Model
{
    protected $table = 'nursing_costs';
    protected $appends = ['lowest_cost', 'highest_cost'];
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'type',
        'hire_rule',
        'name',
        'description',
        'cost'
    ];

    public function getLowestCostAttribute()
    {
        return static::where('user_id', $this->user_id)
            ->where('type', $this->type)
            ->min('cost');
    }

    public function getHighestCostAttribute()
    {
        return static::where('user_id', $this->user_id)
            ->where('type', $this->type)
            ->max('cost');
    }
}