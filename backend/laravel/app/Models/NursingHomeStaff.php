<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class NursingHomeStaff extends Model
{
    protected $table = 'nursing_home_staffs';

    protected $fillable = [
        'user_id', 'name', 'responsibility', 'image'
    ];

    public function down(): void
    {
        Schema::dropIfExists('nursing_home_staffs');
    }
}