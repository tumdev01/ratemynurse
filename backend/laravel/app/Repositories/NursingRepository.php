<?php

namespace App\Repositories;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class NursingRepository extends BaseRepository
{

    // public function __construct(
    //     QrCodeService $qrCodeService
    // ){
    //     $this->qrCodeService = $qrCodeService;
    // }

    public function getNursing(array $filters)
    {
        return User::query()
            ->select([
                'users.id',
                'users.name',
                'users.email'
            ])
            ->where('user_type', UserType::NURSING)
            ->get();
    }

    public function listVillagersUnderPolice(int $police_id, array $filters = [])
    {
        $search = Arr::get($filters, 'search');

        return User::query()
            ->with([
                'profile:user_id,tel,address,province_id,district_id,sub_district_id,zipcode',
                'profile.province',
                'profile.district',
                'profile.subDistrict',
            ])
            ->select(['id', 'name', 'avatar'])
            ->withCount([
                'villagerBoxes as box_count' => function (Builder $query) use ($police_id) {
                    $query->where('police_id', $police_id);
                },
            ])
            ->where('type', UserType::VILLAGER)
            ->whereRaw('exists(select * from police_villager where villager_id = users.id and police_id = ?)', [$police_id])
            ->when(! is_null($search), function (Builder $query) use ($search) {
                $search = Str::lower($search);

                $query->where(function (Builder $query) use ($search) {
                    $query->whereRaw('lower(name) like ?', ["%{$search}%"])
                        ->orWhereHas('profile', function (Builder $query) use ($search) {
                            $query->whereRaw('concat(lower(address), lower(tel)) like ?', "%{$search}%");
                        });
                });
            })
            ->paginate();
    }

    /**
     * @param int $id
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function getVillager(int $id)
    {
        return User::query()
            ->with([
                'profile:user_id,date_of_birth,tel,address,id_card,zipcode,province_id,district_id,sub_district_id',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
            ])
            ->select([
                'users.id',
                'users.avatar',
                'users.username',
                'users.name',
                'users.code'
            ])
            ->where('type', UserType::VILLAGER)
            ->withCount(['villagerBoxes as box_count'])
            ->findOrFail($id);
    }

    /**
     * @param array $filters
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVillagerPagination(array $filters)
    {
        $branch = auth()->user()->branch;
        $villager = User::query()
            ->with([
                'profile:user_id,date_of_birth,tel,address,id_card,zipcode,province_id,district_id,sub_district_id',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
            ])
            ->select([
                'users.id',
                'users.avatar',
                'users.username',
                'users.status',
                'users.name',
                'user_profiles.tel',
                'user_profiles.id_card',
                'user_profiles.address',
                'users.ref_id',
                'users.ref_name',
                'users.ref_tel',
            ])
            ->where('type', UserType::VILLAGER)
            ->leftJoinSub(
                UserProfile::query()
                    ->select(['user_id', 'tel', 'id_card', 'address','updated_at']),
                'user_profiles',
                'user_profiles.user_id',
                '=',
                'users.id'
            )
            ->leftJoinSub(
                Branch::query()
                    ->select(['name', 'id']),
                'branches',
                'branches.id',
                '=',
                'users.branch_id'
            )
            ->withCount(['villagerBoxes as box_count']);

        if( auth()->user()->type->value=='ADMIN' ){
                $branch_id = 0;
                if(!is_null($branch)) {
                    $branch_id = $branch->id;
                }
                $villager->where('users.branch_id', $branch_id);

        }
        if (Arr::get($filters, 'order') == null) {
            $villager->orderBy('user_profiles.updated_at', 'desc');
        }
        return datatables($villager)->toJson();

    }

    public function countVillager()
    {
        return User::query()->where('type', UserType::VILLAGER)->count();
    }

    /**
     * @param string $code
     * @return \Illuminate\Database\Eloquent\Model|User
     */
    public function getByCode(string $code)
    {
        return User::query()
            ->where('type', UserType::VILLAGER)
            ->where('code', $code)
            ->firstOrFail();
    }

    /**
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model|User
     */
    public function getByPhone(string $phone)
    {
        return User::query()
        ->where('type', UserType::VILLAGER)
        ->whereHas('profile', function (Builder $query) use ($phone) {
            $query->where('tel', $phone);
        })
        ->firstOrFail();
    }

    /**
     * @param $id
     * @return string
     */
    public function qrCode($id)
    {
        $user = User::query()->findOrFail($id);

        return $this->qrCodeService->generateBase64QrCode($user->code);
    }

    public function listVillagerByBranchId(string $relation, int $id, array $filter)
    {
        $villagers = User::query()
            ->with([
                'profile:user_id,tel,recommender',
                'profile.recommenders'
            ])
            ->select([
                'users.id',
                'users.name',
                'user_profiles.tel',
            ])
            ->leftJoinSub(
                UserProfile::query()
                    ->select(['user_id', 'tel']),
                'user_profiles',
                'user_profiles.user_id',
                '=',
                'users.id'
            )
            ->where([
                ['type', UserType::VILLAGER],
                ['branch_id', $id]
            ]);
        return datatables($villagers)->toJson();
    }
}
