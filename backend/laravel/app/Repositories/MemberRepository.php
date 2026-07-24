<?php

namespace App\Repositories;
use App\Models\Member;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use App\Models\MemberProfile;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\UploadedFile;
use App\Models\Image;
use Yajra\DataTables\DataTables;

class MemberRepository
{
    public function getMemberDataTable(array $filters = [])
    {
        $orderby = Arr::get($filters, 'orderby', 'id') ?: 'id';
        $order   = Arr::get($filters, 'order', 'DESC');

        $query = Member::query()
            ->with([
                'profile:id,user_id,name,province_id',
                'profile.province:id,name',
                'profile.subscription',
                'coverImage:id,user_id,imageable_id,imageable_type,path,is_cover',
            ])
            ->select(['id', 'firstname', 'lastname', 'email', 'phone', 'status', 'created_at']);

        return DataTables::of($query)
            ->addColumn('fullname', fn($m) => trim("{$m->firstname} {$m->lastname}"))
            ->addColumn('province', fn($m) => optional(optional($m->profile)->province)->name ?? '-')
            ->addColumn('cover_image', fn($m) => $m->coverImage ? $m->coverImage->full_path : '')
            ->addColumn('current_package', function ($m) {
                $subscription = optional($m->profile)->subscription;

                if (!$subscription) {
                    return '-';
                }

                if (now()->gt($subscription->end_date)) {
                    return e($subscription->plan) . ' <span class="text-red-600 font-medium">(หมดอายุ)</span>';
                }

                return e($subscription->plan);
            })
            ->rawColumns(['current_package'])
            ->orderColumn($orderby, fn($query, $order) => $query->orderBy($orderby, $order))
            ->make(true);
    }

    public function updateStatus(int $id, bool $status)
    {
        $member = Member::findOrFail($id);
        $member->status = $status;
        $member->save();
        return $member;
    }

    public function softDeleteMember(int $id)
    {
        $member = Member::findOrFail($id);
        $member->delete();
    }

    public function getUser(int $id)
    {
        return Member::query()
            ->with([
                'profile.subscriptions',
                'profile.province',
                'profile.district',
                'profile.subDistrict',
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

    /**
     * Update member profile
     */
    public function update(array $inputs)
    {
        return DB::transaction(function () use ($inputs) {
            
            $member = Member::findOrFail($inputs['user_id']);

            // ===== 1. Update Member Table =====
            $memberData = array_filter([
                'firstname' => $inputs['firstname'] ?? null,
                'lastname'  => $inputs['lastname'] ?? null,
                'email'     => $inputs['email'] ?? null,
                'phone'     => $inputs['phone'] ?? null,
            ], fn($value) => !is_null($value));

            if (!empty($memberData)) {
                $member->update($memberData);
            }

            // ===== 2. Update Member Profile Table =====
            $profileData = array_filter([
                'name' => isset($inputs['firstname'], $inputs['lastname']) 
                    ? $inputs['firstname'] . ' ' . $inputs['lastname'] 
                    : null,
                'email' => $inputs['email'] ?? null,
                'phone' => $inputs['phone'] ?? null,
                'gender' => $inputs['gender'] ?? null,
                'date_of_birth' => $inputs['date_of_birth'] ?? null,
                'address' => $inputs['address'] ?? null,
                'sub_district_id' => $inputs['sub_district_id'] ?? null,
                'district_id' => $inputs['district_id'] ?? null,
                'province_id' => $inputs['province_id'] ?? null,
                'zipcode' => $inputs['zipcode'] ?? null,
                'facebook' => $inputs['facebook'] ?? null,
                'lineid' => $inputs['lineid'] ?? null,
                'cardid' => $inputs['cardid'] ?? null,
            ], fn($value) => !is_null($value));

            $profile = $member->profile()->updateOrCreate(
                ['user_id' => $member->id],
                $profileData
            );

            // ===== 3. Handle Profile Image Upload =====
            if (!empty($inputs['profile_image']) && $inputs['profile_image'] instanceof UploadedFile) {
                $this->saveProfileImage($member, $profile, $inputs['profile_image']);
            }

            // ===== 4. Reload relationships =====
            $profile->load([
                'province',
                'district', 
                'subDistrict',
                'subscriptions',
                'images'
            ]);

            $member->fresh()->load('profile');

            return [
                'member'  => $member,
                'profile' => $profile,
            ];
        });
    }

    /**
     * Save profile image to public/images/member/{user_id}/
     */
    private function saveProfileImage(
        Member $member,
        MemberProfile $profile,
        UploadedFile $file
    ): ?Image
    {
        try {

            // ✅ อ่านข้อมูลไฟล์ทั้งหมดก่อน move
            $originalName = $file->getClientOriginalName();
            $extension    = $file->getClientOriginalExtension();
            $mimeType     = $file->getClientMimeType(); // ✅ ใช้อันนี้
            $size         = $file->getSize();

            \Log::info('Starting profile image upload', [
                'user_id' => $member->id,
                'file_name' => $originalName,
                'file_size' => $size,
                'mime_type' => $mimeType,
            ]);

            // ===== 1. ลบรูปเดิม =====
            $oldImage = Image::where('user_id', $member->id)
                ->where('imageable_id', $profile->id)
                ->where('imageable_type', MemberProfile::class)
                ->where('is_cover', true)
                ->first();

            if ($oldImage) {
                $fullPath = public_path($oldImage->path);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                $oldImage->delete();
            }

            // ===== 2. เตรียม path =====
            $directory = "images/member/{$member->id}";
            $publicDirectory = public_path($directory);

            if (!file_exists($publicDirectory)) {
                mkdir($publicDirectory, 0755, true);
            }

            $filename = 'profile_' . time() . '.' . $extension;
            $relativePath = "{$directory}/{$filename}";

            // ===== 3. move ไฟล์ (ทำครั้งเดียว) =====
            $file->move($publicDirectory, $filename);

            // ===== 4. บันทึก DB (ปลอดภัยแล้ว) =====
            return Image::create([
                'user_id'        => $member->id,
                'imageable_id'   => $profile->id,
                'imageable_type' => MemberProfile::class,
                'name'           => $filename,
                'path'           => $relativePath,
                'filetype'       => $mimeType,
                'filesize'       => $size,
                'is_cover'       => true,
                'type' => 'MEMBER'
            ]);

        } catch (\Throwable $e) {
            \Log::error('Error saving profile image', [
                'user_id' => $member->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

}