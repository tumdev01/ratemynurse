<?php

namespace App\Repositories;

use App\Models\Nursing;
use App\Models\NursingProfile;
use App\Models\Province;
use Illuminate\Support\Arr;
use Yajra\DataTables\DataTables;
use App\Enums\UserType;
use App\Models\Image;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use App\Http\Requests\NursingUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class NursingRepository
{
    public function createNurse(array $input)
    {
        $user = Nursing::create([
            'user_type'     => UserType::NURSING->value,
            'firstname'     => Arr::get($input, 'firstname'),
            'lastname'      => Arr::get($input, 'lastname'),
            'email'         => Arr::get($input, 'email'),
            'password'      => Hash::make(Arr::get($input, 'phone')),
            'status'        => 1,
            'plan'          => 'BASIC',
            'plan_start'    => Carbon::today()->toDateString(),
            'phone'         => Arr::get($input, 'phone')
        ]);

        if ($user && $user->id) {
            $profile = NursingProfile::create([
                'user_id'   => $user->id,
                'name'      => sprintf('%s %s (%s)', $user->firstname, $user->lastname, Arr::get($input, 'nickname')),
                'nickname'  => Arr::get($input, 'nickname'),
                'gender'    => Arr::get($input, 'gender'),
                'care_type' => Arr::get($input, 'care_type'),
                'date_of_birth' => Arr::get($input, 'date_of_birth'),
                'province_id'   => Arr::get($input, 'province_id'),
                'district_id'   => Arr::get($input, 'district_id'),
                'sub_district_id'  => Arr::get($input, 'sub_district_id'),
                'zipcode'       => Arr::get($input, 'zipcode'),
                'certified'     => (bool) Arr::get($input, 'certified', false)
            ]);

            if ($file = Arr::get($input, 'profile_image')) {
                if ($file->isValid()) {
                    $extension = $file->getClientOriginalExtension();
                    $hashedName = md5(uniqid($user->id, true)) . '.' . $extension;
                    $file->move(public_path('images'), $hashedName);

                    Image::create([
                        'user_id'  => $user->id,
                        'type'     => 'NURSING',
                        'name'     => $file->getClientOriginalName(),
                        'path'     => 'images/' . $hashedName,
                        'filetype' => $file->getClientMimeType(),
                        'is_cover' => true,   // fix: กำหนดตรงนี้เลย
                        'imageable_id' => $profile->id,
                        'imageable_type' => 'App\Models\NursingProfile'
                    ]);
                }
            }
        }

        return $user;
    }

    public function updateNurse(Request $request, int $id)
    {
        $user = Nursing::findOrFail($id);

        DB::transaction(function () use ($request, $user) {

            /* ---------- users table ---------- */
            $user->update($request->only([
                'firstname',
                'lastname',
                'email',
                'phone',
            ]));

            /* ---------- profile ---------- */
            $profile = NursingProfile::where('user_id', $user->id)->first();
            if ($profile) {
                $profile->update([
                    'name' => "{$request->firstname} {$request->lastname}",
                    'nickname' => $request->nickname,
                    'gender' => $request->gender,
                    'care_type' => $request->care_type,
                    'date_of_birth' => $request->date_of_birth,
                    'address' => $request->address,
                    'province_id' => $request->province_id,
                    'district_id' => $request->district_id,
                    'sub_district_id' => $request->sub_district_id,
                    'zipcode' => $request->zipcode,
                    'blood' => $request->blood,
                    'certified' => (bool) $request->certified,
                ]);
            }

            /* ---------- profile image ---------- */
            if ($request->hasFile('profile_image')) {

                $file = $request->file('profile_image');

                if ($file->isValid()) {
                    /** ปิด cover เก่า + ลบไฟล์เก่า */
                    $oldCover = Image::where('user_id', $user->id)
                        ->where('type', 'NURSING')
                        ->where('is_cover', true)
                        ->first();

                    if ($oldCover) {
                        if (file_exists(public_path($oldCover->path))) {
                            unlink(public_path($oldCover->path));
                        }

                        $oldCover->update(['is_cover' => false]);
                    }

                    /** upload ใหม่ */
                    $hashedName = md5(uniqid($user->id, true)) . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('images'), $hashedName);

                    /** save db */
                    Image::create([
                        'user_id'  => $user->id,
                        'type'     => 'NURSING',
                        'name'     => $file->getClientOriginalName(),
                        'path'     => 'images/' . $hashedName,
                        'filetype' => $file->getClientMimeType(),
                        'is_cover' => true,
                        'imageable_id' => $profile->id,
                        'imageable_type' => 'App\Models\NursingProfile'
                    ]);
                }
            }
        });

        return $user->fresh();
    }

    public function getNursing(array $filters = [])
    {

        $query = Nursing::query()
        ->with([
            'profile:id,user_id,zipcode,province_id,district_id,sub_district_id,cost,name,certified,skill',
            'profile.province:id,name',
            'profile.district:id,name',
            'profile.subDistrict:id,name',
            'images:user_id,path,is_cover',
            'coverImage:user_id,path,is_cover',
        ])
        ->select(['users.id'])
        ->withCount(['rates as review_count'])
        ->selectSub(function ($q) {
            $q->from('rate_details')
                ->join('rates', 'rate_details.rate_id', '=', 'rates.id')
                ->selectRaw('ROUND(AVG(rate_details.scores), 1)')
                ->where('rates.rateable_type', NursingProfile::class)
                ->whereColumn('rates.user_id', 'users.id');
        }, 'average_score')
        ->whereNull('deleted_at')
        ->where('status', '!=', 0)
        ->orderByRaw('review_count = 0')
        ->orderByDesc('average_score')
        ->orderByDesc('review_count');
        
        if (!empty($filters['limit']) && is_numeric($filters['limit'])) {
            $query->limit((int) $filters['limit']);
        }

        if (isset($filters['certified'])) {
            $certified = (int) $filters['certified'];
            $query->whereHas('profile', function ($q) use ($certified) {
                $q->where('certified', $certified);
            });
        }
        return $query->get();
    }

    public function getNursingWithZone(array $filters = [])
    {
        $orderby   = Arr::get($filters, 'orderby', 'id') ?: 'id';
        $order     = Arr::get($filters, 'order', 'DESC');
        $province  = Arr::get($filters, 'province');
        $zone      = Arr::get($filters, 'zone');
        $additionalLimit = Arr::get($filters, 'additional_limit', 16);
        
        // ถ้าไม่มี zone แต่มี province ให้ดึง zone จาก province
        if (!$zone && $province) {
            $zone = Province::where('code', $province)->value('zone');
        }
        
        // ดึงข้อมูลจากจังหวัดที่เลือก
        $provinceNursings = $this->getNursingQuery($filters)
            ->when(isset($filters['province']), function ($q) use ($filters) {
                $province_id = Province::where('code', $filters['province'])->value('id');
                if ($province_id) {
                    $q->where('province_id', $province_id);
                }
            })
            ->orderBy($orderby, $order)
            ->get();
        
        $provinceNursingIds = $provinceNursings->pluck('id')->toArray();
        
        // ดึงข้อมูลเพิ่มจาก zone (ไม่รวมจังหวัดที่เลือก)
        $zoneNursings = collect();
        if ($zone) {
            $zoneNursings = $this->getNursingQuery($filters)
                ->whereHas('province', function ($q) use ($zone) {
                    $q->where('zone', $zone);
                })
                ->when(isset($filters['province']), function ($q) use ($filters) {
                    $province_id = Province::where('code', $filters['province'])->value('id');
                    if ($province_id) {
                        $q->where('province_id', '!=', $province_id);
                    }
                })
                ->whereNotIn('id', $provinceNursingIds)
                ->inRandomOrder()
                ->limit($additionalLimit)
                ->get();
        }
        
        // รวมข้อมูล: จังหวัดที่เลือกก่อน แล้วตามด้วย zone
        return $provinceNursings->concat($zoneNursings);
    }

    private function getNursingQuery(array $filters = [])
    {
        $certified = Arr::get($filters, 'certified');
        
        $query = NursingProfile::query()
            ->with([
                'owner:id,firstname,lastname,user_type,status',
                'province:id,name,zone',
                'district:id,name',
                'subDistrict:id,name',
                'coverImage:id,imageable_id,imageable_type,path,is_cover',
                'images:id,imageable_id,imageable_type,path,is_cover',
                'rates.rate_details',
            ])
            ->withCount(['rates as review_count'])
            ->select([
                'id', 
                'user_id', 
                'name', 
                'zipcode', 
                'province_id', 
                'district_id', 
                'sub_district_id', 
                'description', 
                'cost'
            ])
            ->selectSub(function ($q) {
                $q->from('rate_details')
                    ->join('rates', 'rate_details.rate_id', '=', 'rates.id')
                    ->selectRaw('AVG(rate_details.scores)')
                    ->whereColumn('rates.rateable_id', 'nursing_profiles.id')
                    ->where('rates.rateable_type', NursingProfile::class);
            }, 'average_score')
            ->whereHas('owner', function ($q) {
                $q->where('status', 1);
            })
            ->whereNull('deleted_at');
        
        // Filter certified
        if (isset($certified)) {
            $query->where('certified', (int) $certified);
        }
        
        return $query;
    }

    public function getNursingPagination(array $filters = [])
    {
        $orderby   = Arr::get($filters, 'orderby', 'id') ?: 'id';
        $order     = Arr::get($filters, 'order', 'DESC');
        $limit     = Arr::get($filters, 'limit', 8);
        $page      = Arr::get($filters, 'page', 1);
        $certified = Arr::get($filters, 'certified');
        $province  = Arr::get($filters, 'province');
        $zone      = Arr::get($filters, 'zone');
        $search    = Arr::get($filters, 'search');

        // ถ้าไม่มี zone แต่มี province ให้ดึง zone จาก province
        if (!$zone && $province) {
            $zone = Province::where('code', $province)->value('zone');
        }

        $query = NursingProfile::query()
            ->with([
                'owner:id,firstname,lastname,user_type,status',
                'province:id,name,zone',
                'district:id,name',
                'subDistrict:id,name',
                'coverImage:id,imageable_id,imageable_type,path,is_cover',
                'images:id,imageable_id,imageable_type,path,is_cover',
                'rates',
                'rates.rate_details:rate_id,scores,scores_for',
            ])
            ->select([
                'id', 
                'user_id', 
                'name', 
                'province_id', 
                'district_id', 
                'sub_district_id', 
                'description', 
                'cost',
                'about',
                'certified',
                'skill',
                'exp_year'
            ])
            ->withCount(['rates as review_count'])
            ->selectSub(function ($q) {
                $q->from('rate_details')
                    ->join('rates', 'rate_details.rate_id', '=', 'rates.id')
                    ->selectRaw('AVG(rate_details.scores)')
                    ->whereColumn('rates.rateable_id', 'nursing_profiles.id')
                    ->where('rates.rateable_type', NursingProfile::class);
            }, 'average_score')
            ->whereHas('owner', function ($q) {
                $q->where('status', 1);
            })
            ->whereNull('deleted_at');

        // Filter certified
        if (isset($certified)) {
            $query->where('certified', (int) $certified);
        }

        // Filter province - แสดงเฉพาะจังหวัดที่เลือก
        if (isset($province)) {
            $province_id = Province::where('code', $province)->value('id');
            if ($province_id) {
                $query->where('province_id', $province_id);
            }
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                ->orWhere('about', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%")
                ->orWhereHas('province', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('district', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('subDistrict', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
            });
        }

        return $query->orderBy($orderby, $order)->paginate($limit, ['*'], 'page', $page);
    }

    public function countByProvince(array $filters = [])
    {
        $certified = Arr::get($filters, 'certified');
        $province  = Arr::get($filters, 'province');
        
        try {
            $query = NursingProfile::query()
                ->whereHas('owner', function ($q) {
                    $q->where('status', 1);
                })
                ->whereNull('deleted_at');
            
            if (isset($filters['province'])) {
                $province_id = Province::where('code', $filters['province'])->value('id');
                if ($province_id) {
                    $query->where('province_id', $province_id);
                }
            }
            
            if (isset($certified)) {
                $query->where('certified', (int) $certified);
            }
            
            return $query->count();
            
        } catch (\Exception $e) {
            \Log::error('countByProvince error: ' . $e->getMessage());
            return 0;
        }
    }

    public function getNursingById(Int $id)
    {
        $query = Nursing::query()
            ->with([
                'profile',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'images:id,user_id,path,is_cover',
                'coverImage:id,user_id,path,is_cover',
                'rates.rate_details',
                'rates.images:id,imageable_id,imageable_type,path',
                'costs',
                'lowestCost',
                'cvs',
                'cvs.images',
                'detail',
                'detail.images:id,detail_id,path',
            ])
            ->select('id','firstname','lastname','phone','email')
            ->withCount(['rates as review_count'])
            ->selectSub(function ($q) {
                $q->from('rate_details')
                    ->join('rates', 'rate_details.rate_id', '=', 'rates.id')
                    ->selectRaw('AVG(rate_details.scores)')
                    ->where('rates.rateable_type', NursingProfile::class)
                    ->whereColumn('rates.user_id', 'users.id');
            }, 'average_score')
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->where('id', $id)
            ->where('user_type', 'NURSING')
            ->first();
        return $query;
    }

    public function getInfo(int $id) 
    {
        $nursing = Nursing::query()
            ->with([
                'profile',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'rates',
                'rates.rate_details:rate_id,scores,scores_for',
                'images:id,user_id,path,is_cover',
                'coverImage:id,user_id,path,is_cover',
            ])
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->where('id', $id)
            ->where('user_type', 'NURSING')
            ->first();

        if (!$nursing) {
            return null;
        }

        // รวม rate_details ทั้งหมดสำหรับ global average
        $allDetails = $nursing->rates->flatMap->rate_details;
        $nursing->global_avg = $allDetails->avg('scores');

        // เพิ่ม avg_scores ให้แต่ละ rate
        $nursing->rates->transform(function ($rate) {
            $rateDetails = $rate->rate_details;
            $rate->avg_scores = $rateDetails->avg('scores');
            return $rate;
        });

        return $nursing;
    }

    public function getNursingDataTable(array $filters = [])
    {
        $orderby = Arr::get($filters, 'orderby', 'id') ?: 'id';
        $order   = Arr::get($filters, 'order', 'DESC');

        $query = Nursing::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,name,description,certified',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'images:user_id,path,is_cover',
                'coverImage:user_id,path,is_cover',
                'rates',
                'rates.rate_details:rate_id,scores,scores_for',
            ])
            ->select(['users.id','users.status'])
            ->whereNull('users.deleted_at')
            ->where('users.user_type', 'NURSING');

        return DataTables::of($query)
            ->addColumn('name', fn($n) => optional($n->profile)->name ?? '-')
            ->addColumn('cover_image', fn($n) => $n->coverImage ? $n->coverImage->full_path : '')
            ->addColumn('average_score', function ($n) {
                $allDetails = $n->rates->flatMap->rate_details; // รวม rate_details ทั้งหมด
                return $allDetails->count() > 0 
                    ? number_format($allDetails->avg('scores'), 2) 
                    : '-';
            })
            ->addColumn('review_count', function ($n) {
                return $n->rates->count();
            })
            ->addColumn('action', fn($n) => '<a href="#" class="text-blue-600 hover:underline">แก้ไข</a>')
            ->rawColumns(['cover_image', 'action'])
            ->orderColumn($orderby, fn($query, $order) => $query->orderBy($orderby, $order))
            ->make(true);
    }

    public function createOrUpdateHistory(
        array $data,
        $images,
        int $id
    ) {
        $nursing = Nursing::findOrFail($id);
        
        \Log::info('Repository - Images received:', [
            'images' => $images,
            'is_array' => is_array($images),
            'count' => is_array($images) ? count($images) : 0
        ]);

        DB::transaction(function () use ($nursing, $data, $images) {
            $cvs = $nursing->cvs()->updateOrCreate(
                ['user_id' => $nursing->id],
                $data
            );

            if ($images && is_array($images) && count($images) > 0) {
                $storagePath = public_path('cv');
                
                if (!file_exists($storagePath)) {
                    mkdir($storagePath, 0755, true);
                }

                foreach ($images as $file) {
                    if (!$file || !$file->isValid()) {
                        \Log::warning('Invalid file skipped');
                        continue;
                    }

                    try {
                        $name = md5(uniqid(rand(), true)) . '.' . $file->getClientOriginalExtension();
                        $file->move($storagePath, $name);

                        $cvs->images()->create([
                            'user_id' => $nursing->id,
                            'cv_id' => $cvs->id,
                            'name' => $file->getClientOriginalName(),
                            'path' => 'cv/' . $name,
                            'filetype' => $file->getClientMimeType(),
                        ]);
                        
                        \Log::info('Image saved successfully:', ['name' => $name]);
                    } catch (\Exception $e) {
                        \Log::error('Error saving image:', ['error' => $e->getMessage()]);
                    }
                }
            } else {
                \Log::warning('No valid images to process');
            }
        });

        return true;
    }

    public function createOrUpdateDetail(
        array $data,
        $images,
        int $id
    ) {

        $nursing = Nursing::findOrFail($id);
        
        \Log::info('Repository - Images received:', [
            'images' => $images,
            'is_array' => is_array($images),
            'count' => is_array($images) ? count($images) : 0
        ]);

        DB::transaction(function () use ($nursing, $data, $images) {
            $detail = $nursing->detail()->updateOrCreate(
                ['user_id' => $nursing->id],
                $data
            );

            if ($images && is_array($images) && count($images) > 0) {
                $storagePath = public_path('images/detail/');
                
                if (!file_exists($storagePath)) {
                    mkdir($storagePath, 0755, true);
                }

                foreach ($images as $file) {
                    if (!$file || !$file->isValid()) {
                        \Log::warning('Invalid file skipped');
                        continue;
                    }

                    try {
                        $name = md5(uniqid(rand(), true)) . '.' . $file->getClientOriginalExtension();
                        $file->move($storagePath, $name);

                        $detail->images()->create([
                            'user_id' => $nursing->id,
                            'detail_id' => $detail->id,
                            'filename' => $file->getClientOriginalName(),
                            'path' => 'images/detail/' . $name,
                            'filetype' => $file->getClientMimeType(),
                        ]);
                        
                        \Log::info('Image saved successfully:', ['name' => $name]);
                    } catch (\Exception $e) {
                        \Log::error('Error saving image:', ['error' => $e->getMessage()]);
                    }
                }
            } else {
                \Log::warning('No valid images to process');
            }
        });

        return true;

    }
}
