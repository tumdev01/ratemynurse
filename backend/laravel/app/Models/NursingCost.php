<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NursingCost extends Model {
    protected $table = 'nursing_costs';

    protected $fillable = [
        'user_id',
        'type',
        'hire_rule',
        'name',
        'description',
        'cost',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}