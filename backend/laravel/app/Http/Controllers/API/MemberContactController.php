<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\MemberContactCreateRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\API\MemberApiRepository;
use App\Models\Nursing;
use App\Models\NursingHome;
use App\Models\NursingProfile;
use App\Models\NursingHomeProfile;

class MemberContactController extends Controller
{
    protected $memberApiRepository;

    public function __construct(MemberApiRepository $memberApiRepository) {
        $this->memberApiRepository = $memberApiRepository;
    }

    public function create(MemberContactCreateRequest $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->user_type !== 'MEMBER') {
            return response()->json([
                'success' => false,
                'message' => 'Only member can create contact'
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $contact = $this->memberApiRepository->createContactForMember(
                $user,
                $request->validated()
            );

            // Send notification to member
            $user->notifications()->create([
                'title' => 'ส่งคำขอการติดต่อของท่านแล้ว',
                'message' => 'ข้อมูลของคุณถูกส่งให้ผู้ให้บริการแล้ว รอการติดต่อกลับเร็วๆ นี้',
                'type' => 'USER',
                'is_read' => 0,
                'user_id' => $user->id
            ]);

            // Send notification to provider
            $this->sendNotificationToProvider($contact);

            return response()->json([
                'success' => true,
                'data' => $contact
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_CONFLICT); // 409 Conflict สำหรับข้อมูลซ้ำ
        }
    }

    /**
     * Send notification to provider
     */
    private function sendNotificationToProvider(MemberContact $contact): void
    {
        $provider = $this->getProviderFromContact($contact);

        if ($provider) {
            $provider->notifications()->create([
                'title' => 'คุณมีการติดต่อใหม่',
                'message' => 'ระบบได้ส่งรายละเอียดติดต่อจากผู้ใช้ โปรดตรวจสอบและติดต่อผู้ใช้',
                'type' => 'USER',
                'is_read' => 0,
                'user_id' => $provider->id
            ]);
        }
    }

    /**
     * Get provider user from contact
     */
    private function getProviderFromContact(MemberContact $contact): ?User
    {
        $profile = match ($contact->provider_role) {
            'NURSING' => NursingProfile::find($contact->provider_id),
            'NURSING_HOME' => NursingHomeProfile::find($contact->provider_id),
            default => null
        };

        if (!$profile) {
            return null;
        }

        return match ($contact->provider_role) {
            'NURSING' => Nursing::find($profile->user_id),
            'NURSING_HOME' => NursingHome::find($profile->user_id),
            default => null
        };
    }

    public function getContacts() {
        $user = auth()->user();
        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        $result = $this->memberApiRepository->getContacts($user->id);

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200);
    }

    public function getContactById(Request $request) {
        dd($request->all());
    }

    public function deleteContact(Request $request) {
        dd($request->all());
    }

    public function updateContact(Request $request) {
        dd($request->all());
    }
}