<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberContact extends Model
{
    use HasFactory;

    protected $table = 'member_contacts';

    protected $fillable = [
        'member_id',
        'provider_id',
        'provider_role',
        'provider_type',
        'description',
        'start_date',
        'end_date',
        'facebook',
        'lineid',
        'email',
        'phone',
    ];
}