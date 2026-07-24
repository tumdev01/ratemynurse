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
use Intervention\Image\Facades\Image as InterventionImage;

class NursingRepository
{
    // ขนาดรูปปกที่การ์ดพยาบาลใช้แสดงผลจริง (nursing-frontend.js/nursing-grid-frontend.js) — crop
    // ให้พอดีตอนอัปโหลดเลย กันภาพอัตราส่วนแปลกๆ ถูก browser ยืด/บีบไม่สวยตอนแสดงผล
    private const COVER_IMAGE_WIDTH = 314;
    private const COVER_IMAGE_HEIGHT = 240;

    // ตำแหน่งแนวตั้งที่จะเก็บไว้หลัง crop: 0 = ชิดขอบบนสุด (ตัดหัวติดขอบ เจอปัญหาจริงว่าเหลือแต่หัว), 0.5 =
    // กึ่งกลางภาพ (บั๊กเดิมที่ตัดหัวหายไปเลยสำหรับภาพคนแนวตั้ง) ค่านี้อยู่กึ่งกลางระหว่างสองอย่าง เก็บหัว+
    // ไหล่+อกส่วนบนไว้แทนที่จะเหลือแต่หน้า — ยังเป็นค่าประมาณ ไม่ใช่ face detection จริง ถ้าเจอภาพที่ยัง
    // ครอปไม่สวยอีกให้ปรับค่านี้ต่อได้
    private const COVER_IMAGE_VERTICAL_BIAS = 0.35;

    // resize ให้ด้านที่พอดี (คล้าย CSS background-size:cover) แล้ว crop ส่วนเกินออกเอง แทนการใช้
    // fit()+position ของ Intervention เพราะ position รับได้แค่ชื่อ preset (top/center/bottom) ปรับละเอียด
    // ไม่ได้ ส่วนนี้คำนวณ offset เองเพื่อคุมตำแหน่ง crop แนวตั้งแบบเป็นเปอร์เซ็นต์ได้
    private function saveCroppedCoverImage($file, string $hashedName): void
    {
        $destination = public_path('images/' . $hashedName);
        $targetWidth = self::COVER_IMAGE_WIDTH;
        $targetHeight = self::COVER_IMAGE_HEIGHT;

        $image = InterventionImage::make($file->getRealPath());

        $scale = max($targetWidth / $image->width(), $targetHeight / $image->height());
        $scaledWidth = (int) round($image->width() * $scale);
        $scaledHeight = (int) round($image->height() * $scale);

        $image->resize($scaledWidth, $scaledHeight);

        $cropX = (int) max(0, round(($scaledWidth - $targetWidth) / 2));
        $cropY = (int) max(0, round(self::COVER_IMAGE_VERTICAL_BIAS * ($scaledHeight - $targetHeight)));

        $image->crop($targetWidth, $targetHeight, $cropX, $cropY)
            ->save($destination, 90);
    }

    public function createNurse(array $input)
    {
        $user = Nursing::create([
            'user_type'     => UserType::NURSING->value,
            'firstname'     => Arr::get($input, 'firstname'),
            'lastname'      => Arr::get($input, 'lastname'),
            'email'         => Arr::get($input, 'email'),
            'password'      => Hash::make(Arr::get($input, 'phone')),
            'status'        => 1,
            'phone'         => Arr::get($input, 'phone'),
            'plan'          => 'BASIC',
            'plan_start'    => now()->toDateString(),
        ]);

        if ($user && $user->id) {
            $profile = NursingProfile::create([
                'user_id'   => $user->id,
                'name'      => sprintf('%s %s (%s)', $user->firstname, $user->lastname, Arr::get($input, 'nickname')),
                'nickname'  => Arr::get($input, 'nickname'),
                'gender'    => Arr::get($input, 'gender'),
                'date_of_birth' => Arr::get($input, 'date_of_birth'),
                'province_id'   => Arr::get($input, 'province_id'),
                'district_id'   => Arr::get($input, 'district_id'),
                'sub_district_id'  => Arr::get($input, 'sub_district_id'),
                'zipcode'       => Arr::get($input, 'zipcode'),
                'address'       => Arr::get($input, 'address'),
                'certified'     => filter_var(Arr::get($input, 'certified', false), FILTER_VALIDATE_BOOLEAN),
                'care_type'     => Arr::get($input, 'care_type'),
            ]);

            $profile->subscriptions()->create([
                'plan' => 'BASIC',
                'start_date' => now(),
            ]);

            if ($file = Arr::get($input, 'profile_image')) {
                if ($file->isValid()) {
                    $extension = $file->getClientOriginalExtension();
                    $hashedName = md5(uniqid($user->id, true)) . '.' . $extension;
                    $this->saveCroppedCoverImage($file, $hashedName);

                    Image::create([
                        'user_id'  => $user->id,
                        'type'     => 'NURSING',
                        'name'     => $file->getClientOriginalName(),
                        'path'     => 'images/' . $hashedName,
                        'filetype' => $file->getClientMimeType(),
                        'is_cover' => true,   // fix: กำหนดตรงนี้เลย
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
                    'date_of_birth' => $request->date_of_birth,
                    'address' => $request->address,
                    'province_id' => $request->province_id,
                    'district_id' => $request->district_id,
                    'sub_district_id' => $request->sub_district_id,
                    'zipcode' => $request->zipcode,
                    'blood' => $request->blood,
                    'certified' => $request->boolean('certified'),
                    'care_type' => $request->care_type,
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
                    $this->saveCroppedCoverImage($file, $hashedName);

                    /** save db */
                    Image::create([
                        'user_id'  => $user->id,
                        'type'     => 'NURSING',
                        'name'     => $file->getClientOriginalName(),
                        'path'     => 'images/' . $hashedName,
                        'filetype' => $file->getClientMimeType(),
                        'is_cover' => true,
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
                'profile:id,user_id,zipcode,province_id,district_id,sub_district_id,cost,name,certified',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'images:user_id,path,is_cover',
                'coverImage:user_id,path,is_cover'
            ])
            ->select([
                'users.id',
            ])
            ->whereNull('deleted_at')
            ->where('status', '!=', 0);
        
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

    // สำหรับ shortcode nursings-specific เท่านั้น — มิเรอร์จาก
    // NursingHomeRepository::getNursingHomesByIds() แต่ query root เป็น users (Nursing::class)
    // ต่างจาก NursingHomeProfile ที่ query root เป็น profile ตรงๆ เพราะ nursing-info/{id} ใน frontend
    // อ้างอิง users.id เสมอ ไม่ใช่ nursing_profiles.id — review_count/average_score ของ getNursing()
    // เดิมไม่เคยถูกคำนวณเลย (ไม่มี field นี้จริงในผลลัพธ์) เลยคำนวณให้ถูกต้องในนี้แทน
    public function getNursingsByIds(array $ids)
    {
        if (empty($ids)) {
            return collect();
        }

        return Nursing::query()
            ->select(['users.id'])
            ->with([
                'profile:id,user_id,zipcode,province_id,district_id,sub_district_id,cost,name,skill,certified',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'profile.costs',
                'coverImage:user_id,path,is_cover',
            ])
            ->selectSub(function ($q) {
                $q->from('nursing_profiles')
                    ->join('rates', function ($join) {
                        $join->on('rates.rateable_id', '=', 'nursing_profiles.id')
                            ->where('rates.rateable_type', NursingProfile::class);
                    })
                    ->join('rate_details', 'rate_details.rate_id', '=', 'rates.id')
                    ->selectRaw('COUNT(rate_details.id)')
                    ->whereColumn('nursing_profiles.user_id', 'users.id');
            }, 'review_count')
            ->selectSub(function ($q) {
                $q->from('nursing_profiles')
                    ->join('rates', function ($join) {
                        $join->on('rates.rateable_id', '=', 'nursing_profiles.id')
                            ->where('rates.rateable_type', NursingProfile::class);
                    })
                    ->join('rate_details', 'rate_details.rate_id', '=', 'rates.id')
                    ->selectRaw('AVG(rate_details.scores)')
                    ->whereColumn('nursing_profiles.user_id', 'users.id');
            }, 'average_score')
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->whereIn('users.id', $ids)
            ->orderByRaw('FIELD(users.id, ' . implode(',', $ids) . ')')
            ->get();
    }

    // เดิมไม่มี method นี้เลย ทั้งที่ API\NursingController::getNursingPagination() เรียกใช้อยู่แล้ว
    // (พังด้วย BadMethodCallException) มิเรอร์จาก NursingHomeRepository::countByProvince()
    public function countByProvince(array $filters = [])
    {
        $certified = Arr::get($filters, 'certified');
        $province = Arr::get($filters, 'province');
        $search = Arr::get($filters, 'search');

        $query = Nursing::query()
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->whereHas('profile', function ($q) use ($certified, $province, $search) {
                if (isset($certified)) {
                    $q->where('certified', (int) $certified);
                }
                if (!empty($province)) {
                    $province_id = Province::where('code', $province)->value('id');
                    $q->where('province_id', $province_id);
                }
                if (!empty($search)) {
                    $q->where('name', 'like', "%{$search}%");
                }
            });

        return $query->count();
    }

    // เดิมไม่มี method นี้เลยเช่นกัน — API\NursingController::getNursingPagination() เรียกตอนจังหวัดที่เลือก
    // มีผลลัพธ์น้อย (<=10) เพื่อดึงรายชื่อเพิ่มจากโซนเดียวกันมาแสดงต่อท้าย มิเรอร์จาก
    // NursingHomeRepository::getNursingHomeWithZone()
    public function getNursingWithZone(array $filters = [])
    {
        $province = Arr::get($filters, 'province');
        $zone = Arr::get($filters, 'zone');
        $additionalLimit = Arr::get($filters, 'additional_limit', 10);

        // ถ้าไม่มี zone แต่มี province ให้ดึง zone จาก province
        if (!$zone && $province) {
            $zone = Province::where('code', $province)->value('zone');
        }

        // ดึงข้อมูลจากจังหวัดที่เลือกก่อน
        $provinceNursings = $this->getNursingsBaseQuery($filters)
            ->when(!empty($province), function ($q) use ($province) {
                $province_id = Province::where('code', $province)->value('id');
                $q->whereHas('profile', function ($pq) use ($province_id) {
                    $pq->where('province_id', $province_id);
                });
            })
            ->get();

        $provinceNursingIds = $provinceNursings->pluck('id')->toArray();

        // ดึงข้อมูลเพิ่มจาก zone เดียวกัน (ไม่รวมจังหวัดที่เลือกไปแล้ว)
        $zoneNursings = collect();
        if ($zone) {
            $zoneNursings = $this->getNursingsBaseQuery($filters)
                ->whereHas('profile.province', function ($q) use ($zone) {
                    $q->where('zone', $zone);
                })
                ->when(!empty($province), function ($q) use ($province) {
                    $province_id = Province::where('code', $province)->value('id');
                    $q->whereHas('profile', function ($pq) use ($province_id) {
                        $pq->where('province_id', '!=', $province_id);
                    });
                })
                ->whereNotIn('id', $provinceNursingIds)
                ->inRandomOrder()
                ->limit($additionalLimit)
                ->get();
        }

        return $provinceNursings->concat($zoneNursings);
    }

    private function getNursingsBaseQuery(array $filters = [])
    {
        $certified = Arr::get($filters, 'certified');
        $search = Arr::get($filters, 'search');

        return Nursing::query()
            ->with([
                'profile:id,user_id,zipcode,province_id,district_id,sub_district_id,cost,name,certified',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'images:user_id,path,is_cover',
                'coverImage:user_id,path,is_cover',
            ])
            ->select(['users.id'])
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->when(isset($certified), function ($q) use ($certified) {
                $q->whereHas('profile', function ($pq) use ($certified) {
                    $pq->where('certified', (int) $certified);
                });
            })
            ->when(!empty($search), function ($q) use ($search) {
                $q->whereHas('profile', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%");
                });
            });
    }

    public function getNursingPagination(array $filters = [])
    {
        $order = Arr::get($filters, 'order', 'DESC');
        $orderby = Arr::get($filters, 'orderby', 'created_at');
        $limit = Arr::get($filters, 'limit', 10); // ตั้งค่า default limit
        $certified = Arr::get($filters, 'certified', 0);
        $query = Nursing::query()
            ->with([
                'profile:user_id,zipcode,province_id,district_id,sub_district_id,cost,name,certified',
                'profile.province:id,name',
                'profile.district:id,name',
                'profile.subDistrict:id,name',
                'images:user_id,path,is_cover',
                'coverImage:user_id,path,is_cover'
            ])
            ->select(['users.id'])
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->orderBy($orderby, $order);

        if (isset($filters['certified'])) {
            $certified = (int) $filters['certified'];
            $query->whereHas('profile', function ($q) use ($certified) {
                $q->where('certified', $certified);
            });
        }
        return $query->paginate($limit);
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
                'costs',
                'lowestCost',
                'cvs',
                'cvs.images',
                'detail',
                'detail.images:id,detail_id,path'
            ])
            ->select('id','firstname','lastname','phone','email')
            ->withCount(['rates as review_count'])
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
            ->select(['users.id','users.status', 'users.phone', 'users.email'])
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

    public function softDeleteNurse(int $id)
    {
        $nurse = Nursing::findOrFail($id);
        $nurse->deleted_at = now();
        $nurse->save();
    }

    public function updateStatus(int $id, bool $status)
    {
        $nurse = Nursing::findOrFail($id);
        $nurse->status = $status;
        $nurse->save();
        return $nurse;
    }
}
