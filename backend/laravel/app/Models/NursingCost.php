<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NursingCost extends Model {
    protected $table = 'nursing_costs';

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'description',
        'cost_per_day',
        'cost_per_month'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}