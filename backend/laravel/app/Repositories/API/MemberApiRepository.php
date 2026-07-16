<?php

namespace App\Repositories\API;
use App\Models\User;
use App\Models\MemberContact;
use App\Models\Nursing;
use App\Models\NursingHome;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
class MemberApiRepository {

    public function createContactForMember(User $member, array $data): MemberContact
    {
        $provider = $this->resolveProvider(
            $data['provider_role'],
            $data['provider_id']
        );

        return DB::transaction(function () use ($member, $provider, $data) {
            // ตรวจสอบว่ามีข้อมูลซ้ำหรือไม่
            $existing = MemberContact::where('member_id', $member->id)
                ->where('provider_id', $provider->profile->id)
                ->where('provider_role', $data['provider_role'])
                ->where('type', 'USER')
                ->first();

            if ($existing) {
                throw new \Exception('มีข้อมูลการติดต่อนี้อยู่แล้ว');
            }

            return MemberContact::create([
                'member_id'     => $member->id,
                'provider_id'   => $provider->profile->id,
                'provider_role' => $data['provider_role'],
                'provider_type' => get_class($provider),
                'type'          => 'USER',
                'description'   => $data['description'],
                'start_date'    => $data['start_date'],
                'end_date'      => $data['end_date'],
                'phone'         => $data['phone'],
                'email'         => $data['email'] ?? null,
                'lineid'        => $data['line'] ?? null,
                'facebook'      => $data['facebook'] ?? null,
            ]);
        });
    }

    protected function resolveProvider(string $role, int $id)
    {
        return match ($role) {
            'NURSING'      => Nursing::findOrFail($id),
            'NURSING_HOME' => NursingHome::findOrFail($id),
            default        => throw new InvalidArgumentException('Invalid provider role'),
        };
    }

    public function getContacts(Int $user_id) {
        $contact = MemberContact::where('member_id', $user_id)->get();
        print_r($contact);
    }
}