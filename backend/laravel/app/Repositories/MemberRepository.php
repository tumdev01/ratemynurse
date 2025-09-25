<?php

namespace App\Repositories;
use App\Models\Member;
class MemberRepository
{
    public function getUser(int $id)
    {
        return Member::query()
            ->with([
                'profile',
                'images',
                'coverImage'
            ])
            ->addSelect('users.*')
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->where('id', $id)
            ->where('user_type', 'MEMBER')
            ->first();
    }
}