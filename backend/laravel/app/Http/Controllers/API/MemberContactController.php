<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\MemberContactCreateRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\API\MemberApiRepository;
use App\Models\User;
use App\Models\Member;
use App\Models\Nursing;
use App\Models\NursingHome;
use App\Models\NursingProfile;
use App\Models\NursingHomeProfile;
use App\Models\MemberContact;
use Illuminate\Database\QueryException;
use App\Services\MemberContact\ActionMemberContact;
use Illuminate\Auth\Access\AuthorizationException;

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

        } catch (QueryException $e) {

            if ($e->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'code' => 'DUPLICATE_CONTACT',
                    'message' => 'คุณได้ติดต่อผู้ให้บริการรายนี้ไปแล้ว'
                ], Response::HTTP_CONFLICT);
            }

            throw $e;
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
            'NURSING' => NursingProfile::find($contact->provider_profile_id),
            'NURSING_HOME' => NursingHomeProfile::find($contact->provider_profile_id),
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

    public function getContacts(Request $request) 
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $perPage = $request->get('per_page', 20); // default 20 items per page
        $useCache = filter_var(
            $request->get('use_cache', true),
            FILTER_VALIDATE_BOOLEAN
        ); // แปลง 'false'/'0'/'no' (string) → false ให้ถูกต้อง

        $result = $this->memberApiRepository->getContacts(
            $user->id, 
            $perPage,
            $useCache
        );

        return response()->json([
            'success' => true,
            'data' => $result->items(), // items ในหน้านั้น
            'pagination' => [
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
                'from' => $result->firstItem(),
                'to' => $result->lastItem(),
            ]
        ], 200);
    }

    public function getContactById(Int $id) {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $contact = $this->memberApiRepository->getContactById($user->id, $id);

        return response()->json([
            'success' => true,
            'data' => $contact
        ], 200);
    }

    public function getContactByIdForProvider(Int $id)
    {
        $contact = $this->memberApiRepository->getContactByIdForProvider($id);

        return response()->json([
            'success' => true,
            'data' => $contact
        ], 200);
    }

    public function deleteContact(Request $request) {
        dd($request->all());
    }

    public function updateContact(Request $request) {
        dd($request->all());
    }

    public function providerContactAccept(
        Request $request,
        ActionMemberContact $service
    ) {
        $request->validate([
            'contact_id' => ['required', 'integer', 'exists:member_contacts,id'],
        ]);

        try {
            $contact = $service->setAccept(
                $request->input('contact_id'),
                $request->user()
            );

            if ( $contact ) {
                // send to provider
                $provider = $contact->provider_role === 'NURSING_HOME'
                    ? \App\Models\NursingHomeProfile::find($contact->provider_profile_id)
                    : NursingProfile::find($contact->provider_profile_id);
                $name = $provider->name ?? '';
                $provider_name = "({$name})";
                // send to member
                $member = Member::find($contact->member_id);
                $member->notifications()->create([
                    'title' => 'คุณมีการติดต่อใหม่',
                    'message' => "ผู้ให้บริการ {$provider_name} ตอบรับการนัดหมายของคุณแล้ว",
                    'type' => 'USER',
                    'is_read' => 0,
                    'user_id' => $member->id
                ]);

                $this->memberApiRepository->invalidateMemberContactsCache($contact->member_id);
            }

            return response()->json([
                'success' => true,
                'message' => 'ยืนยันการนัดหมายเรียบร้อยแล้ว',
                'data' => $contact,
            ]);
            
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403); // 403 Forbidden
        }
    }
}