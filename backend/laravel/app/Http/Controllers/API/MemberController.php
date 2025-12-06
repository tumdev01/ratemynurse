<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\MemberRepository;
use App\Http\Requests\MemberCreateRequest;
use App\Http\Resources\MemberResource;

class MemberController extends Controller 
{
    protected $member_repository;

    public function __construct(MemberRepository $member_repository)
    {
        $this->member_repository = $member_repository;
    }

    public function getUserInfo(Request $request)
    {
        $member = $request->user();
        $result = $this->member_repository->getUser($member->id);
        return response()->json([
            'status' => 'success',
            'data'   => new MemberResource($result),
        ]);
    }

    public function create(MemberCreateRequest $request)
    {
        try {
            $member = $this->member_repository->store($request->all());
            if (!$member || !$member->exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถบันทึกผู้ใช้ได้',
                ], 500);
            }

            if (!method_exists($member, 'createToken')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create token for user',
                ], 500);
            }

            $token = $member->createToken('api-token')->plainTextToken;

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate access token',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => new MemberResource($member),
                    'access_token' => $token,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}