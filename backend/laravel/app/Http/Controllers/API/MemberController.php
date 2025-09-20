<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\MemberRepository;

class MemberController extends Controller 
{
    protected $member_repository;

    public function __construct(MemberRepository $member_repository)
    {
        $this->member_repository = $member_repository;
    }

    public function getMemberDetail(Request $request)
    {
        $member = $request->user();
        $result = $this->member_repository->getUser($member->id);
        return response()->json([
            'status' => 'success',
            'data'   => $result,
        ]);
    }
}