<?php

namespace App\Repositories\API;

use App\Models\User;
use App\Models\Member;
use App\Models\MemberContact;
use App\Models\Nursing;
use App\Models\NursingHome;
use App\Models\NursingProfile;
use App\Models\NursingHomeProfile;
use App\Models\RateDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
class MemberApiRepository
{
    public function getContacts(int $memberId, int $perPage = 20, bool $useCache = true)
    {
        if (!$useCache) {
            return $this->fetchContactsWithProviders($memberId, $perPage);
        }

        // Version-key strategy: เมื่อมีการ create/update/accept จะ bump version
        // → cache key ใหม่ → query DB ใหม่ → ได้ของอัพเดททันที
        $page = request()->get('page', 1);
        $version = Cache::get($this->contactsCacheVersionKey($memberId), 1);
        $cacheKey = "member_contacts_{$memberId}_v{$version}_page_{$page}";
        $cacheDuration = 1800; // 30 นาที (มี invalidation รองรับแล้ว ไม่ต้องพึ่ง TTL สั้น)

        return Cache::remember($cacheKey, $cacheDuration, function () use ($memberId, $perPage) {
            return $this->fetchContactsWithProviders($memberId, $perPage);
        });
    }

    private function contactsCacheVersionKey(int $memberId): string
    {
        return "member_contacts_version_{$memberId}";
    }

    private function flushContactsCache(int $memberId): void
    {
        $key = $this->contactsCacheVersionKey($memberId);
        $current = Cache::get($key, 1);
        Cache::forever($key, $current + 1);
    }

    public function invalidateMemberContactsCache(int $memberId): void
    {
        $this->flushContactsCache($memberId);
    }

    private function fetchContactsWithProviders(int $memberId, int $perPage)
    {
        // ใช้ Eager Loading เพื่อลด N+1 Query
        $contacts = MemberContact::where('member_id', $memberId)
            ->select('id', 'member_id', 'provider_user_id', 'provider_role', 'provider_profile_id', 'description', 'start_date', 'end_date', 'phone', 'email', 'lineid', 'facebook', 'created_at', 'updated_at', 'provider_accepted')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Transform data
        $contacts->getCollection()->transform(function ($contact) {
            $providerData = $this->getProviderData($contact);

            return [
                'id' => $contact->id,
                'description' => $contact->description,
                'start_date' => $contact->start_date,
                'end_date' => $contact->end_date,
                'phone' => $contact->phone,
                'email' => $contact->email,
                'lineid' => $contact->lineid,
                'facebook' => $contact->facebook,
                'provider_role' => $contact->provider_role,
                'provider_accepted' => $contact->provider_accepted,
                'created_at' => $contact->created_at,
                'updated_at' => $contact->updated_at,
                'provider' => $providerData
            ];
        });

        return $contacts;
    }

    private function getProviderData($contact): ?array
    {
        if ($contact->provider_role === 'NURSING') {

            $profile = NursingProfile::select('id', 'name')
                ->where('id', $contact->provider_profile_id)
                ->first();
           
            if ($profile) {
                $nursing = Nursing::with([
                        'profile:id,user_id,name,nickname',
                        'coverImage',
                        'lowestCost',
                    ])
                    ->select('id', 'email')
                    ->find($contact->provider_user_id);

                if ($nursing) {
                    $summaryCost = [
                        'lower_cost'  => $nursing->costs()->min('cost') ?? 0,
                        'higher_cost' => $nursing->costs()->max('cost') ?? 0,
                    ];

                    $summaryCostByType = $nursing->costs()
                        ->selectRaw('type, MIN(cost) as lower_cost, MAX(cost) as higher_cost')
                        ->groupBy('type')
                        ->get()
                        ->keyBy('type');

                    // จำนวนรีวิว (นับจาก rates ของ profile)
                    $reviewCount = $profile->rates()->count();

                    // ค่าเฉลี่ยคะแนน (คำนวณจาก rate_details ตรง ๆ)
                    $averageScore = RateDetail::query()
                        ->join('rates', 'rate_details.rate_id', '=', 'rates.id')
                        ->where('rates.rateable_type', NursingProfile::class)
                        ->where('rates.rateable_id', $profile->id)
                        ->avg('rate_details.scores');

                    return [
                        'id'           => $nursing->id,
                        'name'         => $profile->name,
                        'email'        => $nursing->email,
                        'profile'    => array_merge(
                            $profile->toArray(),
                            [
                                'summary_cost'         => $summaryCost,
                                'summary_cost_by_type' => $summaryCostByType,
                            ]
                        ),
                        'coverImage'   => $nursing->coverImage?->full_path,
                        'type'         => 'NURSING',
                        'lowestCost'   => $nursing->lowestCost,
                        'review_count' => $reviewCount,
                        'rate_avg'     => round((float) $averageScore, 1),
                    ];
                }
            }
        } elseif ($contact->provider_role === 'NURSING_HOME') {
            $profile = NursingHomeProfile::
                select('id', 'user_id', 'name', 'address', 'cost_per_month')
                ->find($contact->provider_profile_id);

            if ($profile) {
                $nursingHome = NursingHome::select('id', 'email')
                    ->find($contact->provider_user_id);

                if ($nursingHome) {
                    // จำนวนรีวิว
                    $reviewCount = $profile->rates()->count();

                    // ค่าเฉลี่ยคะแนน
                    $averageScore = RateDetail::query()
                        ->join('rates', 'rate_details.rate_id', '=', 'rates.id')
                        ->where('rates.rateable_type', NursingHomeProfile::class)
                        ->where('rates.rateable_id', $profile->id)
                        ->avg('rate_details.scores');

                    $summaryCost = [
                        'lower_cost'  => $profile->cost_per_month ?? 0,
                        'higher_cost' => $profile->cost_per_month ?? 0,
                    ];

                    return [
                        'id'           => $nursingHome->id,
                        'name'         => $profile->name,
                        'email'        => $nursingHome->email,
                        'profile'    => array_merge(
                            $profile->toArray(),
                            [
                                'summary_cost'         => $summaryCost,
                            ]
                        ),
                        'coverImage'   => $profile->coverImage?->full_path,
                        'type'         => 'NURSING_HOME',
                        'review_count' => $reviewCount,
                        'rate_avg'     => round((float) $averageScore, 1),
                    ];
                }
            }
        }

        return null;
    }

    private function getMemberData(int $member_id): Member
    {
        return Member::with(['profile', 'coverImage'])->findOrFail($member_id);
    }

    public function createContactForMember(User $member, array $data): MemberContact
    {
        $provider = $this->resolveProvider(
            $data['provider_role'],
            $data['provider_id']
        );

        return DB::transaction(function () use ($member, $provider, $data) {
            $provider_id = $data['provider_role'] === 'NURSING'
                ? $provider->id
                : $provider->user_id;
            $profileId = $data['provider_role'] === 'NURSING'
                ? $provider->profile->id
                : $provider->id;

            // ตรวจสอบข้อมูลซ้ำ
            $exactDuplicate = MemberContact::where([
                'member_id'           => $member->id,
                'provider_user_id'         => $provider_id,
                'provider_profile_id' => $profileId,
                'provider_role'       => $data['provider_role'],
                'type'                => 'USER',
                'start_date'          => $data['start_date'],
                'end_date'            => $data['end_date'],
                'phone'               => $data['phone'],
            ])->exists();

            if ($exactDuplicate) {
                throw new \Exception('มีข้อมูลการติดต่อนี้อยู่แล้ว');
            }

            // ตรวจสอบวันที่ทับซ้อน (optional)
            $overlapping = MemberContact::where([
                'member_id'           => $member->id,
                'provider_user_id'         => $provider_id,
                'provider_profile_id' => $profileId,
                'provider_role'       => $data['provider_role'],
                'type'                => 'USER',
            ])
            ->where(function ($query) use ($data) {
                $query->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                      ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                      ->orWhere(function ($q) use ($data) {
                          $q->where('start_date', '<=', $data['start_date'])
                            ->where('end_date', '>=', $data['end_date']);
                      });
            })
            ->exists();

            if ($overlapping) {
                throw new \Exception('มีการจองช่วงวันที่ซ้ำซ้อนกับข้อมูลเดิมอยู่แล้ว');
            }

            $contact = MemberContact::create([
                'member_id'           => $member->id,
                'provider_user_id'         => $provider_id,
                'provider_profile_id' => $profileId,
                'provider_role'       => $data['provider_role'],
                'provider_type'       => get_class($provider),
                'type'                => 'USER',
                'description'         => $data['description'],
                'start_date'          => $data['start_date'],
                'end_date'            => $data['end_date'],
                'phone'               => $data['phone'],
                'email'               => $data['email'] ?? null,
                'lineid'              => $data['lineid'] ?? null,
                'facebook'            => $data['facebook'] ?? null,
            ]);

            $this->flushContactsCache($member->id);

            return $contact;
        });
    }

    protected function resolveProvider(string $role, int $id)
    {
        return match ($role) {
            'NURSING'      => Nursing::findOrFail($id),
            'NURSING_HOME' => NursingHomeProfile::findOrFail($id),
            default        => throw new \InvalidArgumentException('Invalid provider role'),
        };
    }

    public function getContactById(int $memberId, int $contactId): array
    {
        $contact = MemberContact::where('member_id', $memberId)
            ->where('id', $contactId)
            ->select([
                'id',
                'member_id',
                'provider_user_id',
                'provider_role',
                'provider_profile_id',
                'provider_type',
                'provider_accepted',
                'description',
                'start_date',
                'end_date',
                'phone',
                'email',
                'lineid',
                'facebook',
                'created_at',
                'updated_at',
            ])
            ->first();

        if (!$contact) {
            throw new \Exception('ไม่พบข้อมูลการติดต่อ');
        }

        $provider = $this->getProviderData($contact);

        // ดึง provider profile จาก morphTo relation (NursingProfile | NursingHomeProfile)
        $providerProfile = $contact->providerProfile;

        return [
            'id'                => $contact->id,
            'description'       => $contact->description,
            'start_date'        => $contact->start_date,
            'end_date'          => $contact->end_date,
            'phone'             => $contact->phone,
            'email'             => $contact->email,
            'lineid'            => $contact->lineid,
            'facebook'          => $contact->facebook,
            'provider_role'     => $contact->provider_role,
            'provider_accepted' => $contact->provider_accepted,
            'created_at'        => $contact->created_at,
            'updated_at'        => $contact->updated_at,
            'provider'          => $provider,
            'provider_profile'  => $providerProfile,
        ];
    }

    public function getContactByIdForProvider(int $contactId): array
    {
        $contact = MemberContact::where('id', $contactId)
            ->select([
                'id',
                'member_id',
                'provider_user_id',
                'provider_role',
                'provider_profile_id',
                'provider_accepted',
                'description',
                'start_date',
                'end_date',
                'phone',
                'email',
                'lineid',
                'facebook',
                'created_at',
                'updated_at',
            ])
            ->first();

        if (!$contact) {
            throw new \Exception('ไม่พบข้อมูลการติดต่อ');
        }

        $provider = $this->getProviderData($contact);
        $member   = $this->getMemberData($contact->member_id);
        $memberData = [
            'name' => $member->name,
            'coverImage' => $member->coverImage
        ];
        return [
            'id'            => $contact->id,
            'description'   => $contact->description,
            'start_date'    => $contact->start_date,
            'end_date'      => $contact->end_date,
            'phone'         => $contact->phone,
            'email'         => $contact->email,
            'lineid'        => $contact->lineid,
            'facebook'      => $contact->facebook,
            'provider_role' => $contact->provider_role,
            'provider_accepted' => $contact->provider_accepted,
            'created_at'    => $contact->created_at,
            'updated_at'    => $contact->updated_at,
            'provider'      => $provider,
            'member'        => $memberData
        ];
    }
    
}