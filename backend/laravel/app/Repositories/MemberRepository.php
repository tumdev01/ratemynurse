<?php

namespace App\Repositories;
use App\Models\Member;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use App\Models\MemberProfile;
use Illuminate\Support\Facades\DB;
use Exception;
class MemberRepository
{
    public function getUser(int $id)
    {
        return Member::query()
            ->with([
                'profile.subscriptions',
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

    public function store(Array $inputs) {
        DB::beginTransaction();
        try {
            $cardId = Arr::get($inputs, 'cardid');
            $password = Hash::make($cardId);
            $inputs['password'] = $password;
            $inputs['user_type'] = 'MEMBER';
            $inputs['status'] = 1;
            $inputs['role'] = 'MEMBER';
            $inputs['email'] = Arr::get($inputs, 'email');
            $inputs['phone'] = Arr::get($inputs, 'phone');
            $inputs['created_at'] = now();
            $inputs['updated_at'] = now();
            $user = Member::create($inputs);

            if (!$user || !$user->exists) {
                throw new \Exception('Failed to create Member user.');
            }
        
            $name = $user->firstname . ' ' . $user->lastname;
            $profile = new MemberProfile();
            $profile->user_id = $user->id;
            $profile->name = $name;
            $profile->email = $user->email;
            $profile->phone = $user->phone;
            $profile->cardid = $cardId;
            $profile->newsletter = 1;
            $profile->privacy = 1;
            $profile->policy = 1;   
            $profile->save();

            $profile->subscriptions()->create([
                'plan' => 'BASIC', // first time register set to "BASIC"
                'start_date' => now()
            ]);

            $user->notifications()->create([
                'title' => 'RateMyNurse ยินดีต้อนรับ',
                'message' => 'คุณได้สมัครสมาชิกเรียบร้อยแล้ว',
                'type' => 'Models/Member',
                'is_read' => 0,
                'user_id' => $user->id
            ]);
            
            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}