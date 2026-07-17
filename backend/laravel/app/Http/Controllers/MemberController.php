<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserSubscriptionRequest;
use App\Repositories\MemberRepository;
use Illuminate\Http\Request;

class MemberController extends Controller {
    protected $member_repository;

    public function __construct(MemberRepository $member_repository)
    {
        $this->member_repository = $member_repository;
    }

    public function index()
    {
        return view('pages.member.index');
    }

    public function getMemberPagination(Request $request)
    {
        $filters = $request->only(['orderby', 'order']);
        return $this->member_repository->getMemberDataTable($filters);
    }

    public function detailView(int $id)
    {
        $member = $this->member_repository->getUser($id);

        $subscriptionRequest = UserSubscriptionRequest::where('user_id', $id)
            ->whereIn('status', ['awaiting_payment', 'cancelled', 'expired'])
            ->latest()
            ->first();

        return view('pages.member.detail', compact('member', 'subscriptionRequest'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $this->member_repository->updateStatus($id, (bool) $request->status);

        return response()->json([
            'success' => true,
            'message' => "อัพเดทสถานะสมาชิก #{$id} สำเร็จ",
        ]);
    }

    public function create()
    {
        return view('pages.member.create');
    }
}
