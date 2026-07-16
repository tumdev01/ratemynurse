<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Rate;
use App\Models\RateDetail;
use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RateRepository extends BaseRepository
{
    public function create(array $params)
    {
        try {
            return DB::transaction(function () use ($params) {
                // 1) ตรวจสอบ user
                $user = User::findOrFail(Arr::get($params, 'user_id'));

                // 2) ตรวจสอบ author (0 = admin)
                $authorId = (int) Arr::get($params, 'author_id');
                if ($authorId > 0) {
                    User::findOrFail($authorId);
                }

                // 3) สร้าง Rate
                $rate = Rate::create([
                    'user_id'       => $user->id,
                    'author_id'     => $authorId,
                    'text'          => $params['text'] ?? '',
                    'name'          => $params['name'] ?? '',
                    'description'   => $params['description'] ?? '',
                    'user_type'     => $user->user_type,
                    'rateable_id'   => $params['rateable_id'] ?? null,
                    'rateable_type' => $params['rateable_type'] ?? null,
                ]);

                // 4) สร้าง RateDetail
                $scores = Arr::get($params, 'scores', []);
                foreach ($scores as $category => $score) {
                    RateDetail::create([
                        'rate_id'    => $rate->id,
                        'scores_for' => $category,
                        'scores'     => $score,
                    ]);
                }

                // 5) บันทึกรูปภาพ (polymorphic Image → Rate)
                $images = Arr::get($params, 'images', []);
                if (is_array($images)) {
                    foreach ($images as $file) {
                        if (!($file instanceof UploadedFile) || !$file->isValid()) {
                            continue;
                        }

                        $extension = $file->getClientOriginalExtension() ?: $file->extension();
                        $filename  = md5(uniqid('', true)) . '.' . $extension;

                        $file->move(public_path('images'), $filename);

                        Image::create([
                            'user_id'        => $authorId,
                            'imageable_id'   => $rate->id,
                            'imageable_type' => Rate::class,
                            'name'           => $file->getClientOriginalName(),
                            'path'           => 'images/' . $filename,
                            'filetype'       => $file->getClientMimeType(),
                            'is_cover'       => false,
                            'type'           => 'REVIEW',
                        ]);
                    }
                }

                return response()->json([
                    'status'  => 'success',
                    'message' => 'บันทึกสำเร็จ',
                ], 200);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'ไม่สามารถให้คะแนนซ้ำได้สำหรับผู้ใช้คนนี้',
                ], 422);
            }

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
