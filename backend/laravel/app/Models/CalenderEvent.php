<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalenderEvent extends Model
{
    use HasFactory, softDeletes;

    protected $table = 'calendar_events';

    protected $fillable = [
        'member_contact_id',
        'member_id',
        'member_profile_id',
        'provider_id',
        'provider_profile_id',
        'event_type',
        'title',
        'start_date',
        'end_date',
        'status',
    ];

    public function contact()
    {
        return $this->belongsTo(MemberContact::class);
    }
}
