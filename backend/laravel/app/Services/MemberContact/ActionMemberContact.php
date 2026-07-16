<?php
namespace App\Services\MemberContact;

use App\Models\User;
use App\Models\MemberContact;
use Illuminate\Auth\Access\AuthorizationException;

class ActionMemberContact
{
    public function setAccept(int $contact_id, User $user): MemberContact
    {
        $contact = MemberContact::findOrFail($contact_id);

        if (! $this->isCorrectProvider($contact, $user)) {
            throw new AuthorizationException('คุณไม่มีสิทธิ์ยืนยันการติดต่อรายการนี้');
        }

        $contact->update([
            'provider_accepted' => 1,
        ]);

        return $contact;
    }

    protected function isCorrectProvider(MemberContact $contact, User $user): bool
    {
        // ✅ ใช้ method ที่มีอยู่แล้วใน Model
        return $contact->isOwnedByProvider($user);
    }
}