<?php

namespace App\Repositories\API;

use App\Models\Nursing;
use App\Models\NursingProfile;
use App\Models\NursingDetail;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserType;
use App\Models\NursingCvs;
use App\Models\NursingCvImage;
use App\Models\NursingDetailImage;
use App\Models\NursingCost;
use Illuminate\Support\Carbon;
use App\Enums\Skill;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class NursingApiRepository
{
    public function createNurse(array $input)
    {
        return DB::transaction(function () use ($input) {
            $nursing = Nursing::create([
                'firstname'  => $input['firstname'],
                'lastname'   => $input['lastname'],
                'email'      => $input['email'],
                'password'   => Hash::make($input['phone']),
                'phone'      => $input['phone'],
                'status'     => 1,
                'plan'       => 'BASIC',
                'plan_start' => Carbon::today()->toDateString(),
                'user_type'  => 'NURSING',
            ]);

            // If create() failed, throw exception
            if (!$nursing || !$nursing->exists) {
                throw new \Exception('Failed to create Nursing user.');
            }

            $profile = NursingProfile::create([
                'user_id' => $nursing->id,
                'name'    => $nursing->firstname . ' ' . $nursing->lastname,
                'nickname' => Arr::get($input, 'nickname') ?? '',
                'gender'  => Arr::get($input, 'gender') ?? '',
                'blood'   => Arr::get($input, 'blood') ?? '',
                'date_of_birth' => Arr::get($input, 'date_of_birth') ?? '',
                'medical_condition' => Arr::get($input, 'medical_condition') ?? '',
                'history_of_drug_allergy' => Arr::get($input, 'history_of_drug_allergy') ?? '',
            ]);

            if (!$profile || !$profile->exists) {
                throw new \Exception('Failed to create Nursing user.');
            }

            return $nursing;
        });
    }

    public function updateProfile(array $input, Int $user_id)
    {
        DB::beginTransaction();

        try {

            /* -----------------------------
            | 1) Update User + Profile
            ----------------------------- */
            $user = Nursing::findOrFail($user_id);
            $profile = NursingProfile::where('id', Arr::get($input, 'id'))
                        ->where('user_id', $user_id)
                        ->firstOrFail();

            // Update user names
            $user->update([
                'firstname' => Arr::get($input, 'firstname', ''),
                'lastname'  => Arr::get($input, 'lastname', '')
            ]);

            // Update profile
            $profile->update([
                'name'        => $user->firstname.' '.$user->lastname,
                'nickname'    => Arr::get($input, 'nickname', ''),
                'gender'      => Arr::get($input, 'gender', ''),
                'address'     => Arr::get($input, 'address', ''),
                'sub_district_id' => Arr::get($input, 'sub_district_id'),
                'district_id'     => Arr::get($input, 'district_id'),
                'province_id'     => Arr::get($input, 'province_id'),
                'zipcode'     => Arr::get($input, 'zipcode'),
                'blood'       => Arr::get($input, 'blood'),
            ]);

            /* -----------------------------
            | 2) Profile Image Upload
            ----------------------------- */
            if ($file = Arr::get($input, 'profile_image')) {

                if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {

                    $ext = $file->getClientOriginalExtension();
                    $newName = md5(uniqid($user->id, true)).'.'.$ext;

                    $dest = "images/{$newName}";
                    File::ensureDirectoryExists(public_path('images'));
                    $file->move(public_path('images'), $newName);

                    // delete old
                    $oldImage = $user->images()
                                    ->where('type', 'NURSING')
                                    ->where('is_cover', true)
                                    ->first();

                    if ($oldImage) {
                        if (File::exists(public_path($oldImage->path))) {
                            File::delete(public_path($oldImage->path));
                        }
                        $oldImage->delete();
                    }

                    // create new
                    $user->images()->create([
                        'name'         => $file->getClientOriginalName(),
                        'path'         => $dest,
                        'type'         => 'NURSING',
                        'filetype'     => $file->getClientMimeType(),
                        'is_cover'     => true,
                        'imageable_id' => $user->id,
                    ]);
                }
            }

            /* -----------------------------
            | 3) CV: Update Or Create
            ----------------------------- */
            $nursingCv = NursingCvs::updateOrCreate(
                ['user_id' => $user_id],
                [
                    'graducated'        => Arr::get($input, 'graducated'),
                    'edu_ins'           => Arr::get($input, 'edu_ins'),
                    'graducated_year'   => Arr::get($input, 'graduated_year'),
                    'gpa'               => Arr::get($input, 'gpa'),
                    'cert_no'           => Arr::get($input, 'cert_no'),
                    'cert_date'         => Arr::get($input, 'cert_date'),
                    'cert_expire'       => Arr::get($input, 'cert_expire'),
                    'cert_etc'          => Arr::get($input, 'cert_etc'),
                    'extra_courses'     => Arr::get($input, 'extra_courses'),
                    'current_workplace' => Arr::get($input, 'current_workplace'),
                    'department'        => Arr::get($input, 'department'),
                    'position'          => Arr::get($input, 'position'),
                    'exp'               => Arr::get($input, 'exp'),
                    'work_type'         => Arr::get($input, 'work_type'),
                    'extra_shirft'      => Arr::get($input, 'extra_shirft'),
                    'languages'         => Arr::get($input, 'languages'),
                ]
            );

            $cvId = $nursingCv->id;

            /* -----------------------------
            | 5) CV Images — Upload new
            ----------------------------- */
            if (!empty($input['cvs_images'])) {
                foreach ($input['cvs_images'] as $file) {

                    if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {

                        // เก็บชื่อจริงของไฟล์ก่อนแปลง
                        $originalName = $file->getClientOriginalName();

                        $ext = $file->getClientOriginalExtension() ?: 'bin';
                        $fileName = md5(uniqid($user_id, true)) . '.' . $ext;

                        // รองรับทั้งรูปและ PDF
                        $destFolder = public_path('images/cv');
                        File::ensureDirectoryExists($destFolder);

                        $destPath = 'images/cv/' . $fileName;

                        // move file
                        $file->move($destFolder, $fileName);

                        // save record
                        NursingCvImage::create([
                            'user_id'  => $user_id,
                            'cv_id'    => $cvId,
                            'name'     => $originalName,                // ⭐ add original file name
                            'path'     => $destPath,
                            'filetype' => $file->getClientMimeType(),   // image/jpeg หรือ application/pdf
                        ]);
                    }
                }
            }

            /* -----------------------------
            | 6) Nursing Detail
            ----------------------------- */
            $skills = null;
            if (Arr::get($input, 'skills')) {

                $skillKeys = json_decode($input['skills'], true) ?? [];
                $allSkills = Skill::list();
                $formatted = [];

                foreach ($skillKeys as $key) {
                    if (isset($allSkills[$key])) {
                        $formatted[] = [
                            'key'   => $key,
                            'value' => $allSkills[$key],
                        ];
                    }
                }

                $skills = json_encode($formatted);
            }

            $nursingDetail = NursingDetail::updateOrCreate(
                ['user_id' => $user_id],
                [
                    'about'        => Arr::get($input, 'about'),
                    'hire_rules'   => Arr::get($input, 'hire_rules'),
                    'skills'       => $skills,
                    'other_skills' => Arr::get($input, 'other_skills')
                ]
            );

            if(!empty($input['detail_images'])) {
                foreach ($input['detail_images'] as $file) {

                    if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {

                        // เก็บชื่อจริงของไฟล์ก่อนแปลง
                        $originalName = $file->getClientOriginalName();

                        $ext = $file->getClientOriginalExtension() ?: 'bin';
                        $fileName = md5(uniqid($user_id, true)) . '.' . $ext;

                        // รองรับทั้งรูปและ PDF
                        $destFolder = public_path('images/detail');
                        File::ensureDirectoryExists($destFolder);

                        $destPath = 'images/detail/' . $fileName;

                        // move file
                        $file->move($destFolder, $fileName);

                        // save record
                        NursingDetailImage::create([
                            'detail_id'  => $nursingDetail->id,
                            'filename'     => $originalName,                // ⭐ add original file name
                            'path'     => $destPath,
                            'filetype' => $file->getClientMimeType(),   // image/jpeg หรือ application/pdf
                        ]);
                    }
                }
            }

            if (isset($input['costs'])) {
                $decoded = json_decode($input['costs'], true);

                if (!is_array($decoded)) {
                    throw new \Exception("Costs must be a valid JSON format.");
                }

                $upsertData = [];

                foreach ($decoded as $type => $items) {
                    foreach ($items as $hireRule => $costValue) {
                        $upsertData[] = [
                            'user_id'    => $user->id,
                            'type'       => strtoupper(trim($type)),        // normalize
                            'hire_rule'  => strtoupper(trim($hireRule)),    // normalize
                            'name'       => (strtolower($type) === 'daily' ? 'รายวัน' : 'รายเดือน'),
                            'description'=> null,
                            'cost'       => (float) $costValue,             // convert to float
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if (!empty($upsertData)) {
                    NursingCost::upsert(
                        $upsertData,
                        ['user_id', 'type', 'hire_rule'],   // unique key
                        ['cost', 'updated_at']              // fields to update
                    );
                }
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
