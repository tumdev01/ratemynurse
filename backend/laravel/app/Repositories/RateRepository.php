<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Rate;
use App\Models\RateDetail;
use Illuminate\Support\Arr;

class RateRepository extends BaseRepository
{
    public function create(array $params)
    {
        try {
            // 1) ตรวจสอบ user
            $user = User::findOrFail(Arr::get($params, 'user_id'));

            // 2) ตรวจสอบ author (0 = admin)
            $authorId = (int) Arr::get($params, 'author_id');
            if ($authorId > 0) {
                $author = User::findOrFail($authorId);
            }

            // 3) สร้าง Rate
            $rate = Rate::create([
                'user_id'     => $user->id,
                'author_id'   => $authorId,
                'text'        => $params['text'] ?? '',
                'name'        => $params['name'] ?? '',
                'description' => $params['description'] ?? '',
                'user_type'   => $user->user_type,
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

            return response()->json([
                'status' => 'success',
            ], 200);

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ไม่สามารถให้คะแนนซ้ำได้สำหรับผู้ใช้คนนี้',
                ], 422);
            }

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
