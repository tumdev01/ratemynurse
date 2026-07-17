<?php

namespace App\Services;

use App\Enums\NotificationCategory;
use App\Enums\SubscriptionPlan;
use App\Mail\SubscriptionMail;
use App\Models\Notification;
use App\Models\UserSubscription;
use App\Models\UserSubscriptionLog;
use App\Models\UserSubscriptionRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class UserSubmitRenewalService
{
    public function submit(int $userId, int $profileId, string $type, string $plan): UserSubscriptionRequest
    {
        $request = UserSubscriptionRequest::updateOrCreate(
            [
                'user_id' => $userId,
                'type' => $type,
            ],
            [
                'profile_id' => $profileId,
                'plan' => $plan,
                'status' => 'awaiting_payment',
            ]
        );

        $this->log($request, 'submitted', 'user');
        $this->log($request, 'awaiting_payment', 'user');

        $price = number_format(SubscriptionPlan::priceFor($type, $plan));
        $title = 'ส่งคำขอสมัครแพ็กเกจสำเร็จ';
        $message = "คุณได้ส่งคำขอแพ็กเกจ {$plan} ราคา {$price} บาท เรียบร้อยแล้ว กรุณารอการตรวจสอบการชำระเงินจากเจ้าหน้าที่";

        $this->notify($request, $title, $message);
        $this->sendEmail($request, $title, $message);

        return $request;
    }

    public function acceptPayment(int $requestId, int $adminId): UserSubscriptionRequest
    {
        $request = UserSubscriptionRequest::findOrFail($requestId);

        // Soft delete existing subscription for this profile
        UserSubscription::where('subscribable_id', $request->profile_id)
            ->where('subscribable_type', $request->type)
            ->delete();

        // Create new subscription
        $subscription = UserSubscription::create([
            'subscribable_id' => $request->profile_id,
            'subscribable_type' => $request->type,
            'plan' => $request->plan,
            'start_date' => Carbon::now(),
        ]);

        $request->update(['status' => 'payment_accepted']);

        $this->log($request, 'payment_accepted', 'admin', $adminId);

        $title = 'การชำระเงินได้รับการยืนยันแล้ว';
        $message = "แพ็กเกจ {$request->plan} ของคุณเปิดใช้งานแล้ว เริ่มตั้งแต่วันที่ {$subscription->start_date->format('d/m/Y')} ถึง {$subscription->end_date}";

        $this->notify($request, $title, $message);

        $pdf = Pdf::loadView('pdf.subscription-receipt', [
            'userName' => trim($request->user->firstname . ' ' . $request->user->lastname),
            'plan' => $request->plan,
            'price' => number_format(SubscriptionPlan::priceFor($request->type, $request->plan)),
            'startDate' => $subscription->start_date->format('d/m/Y'),
            'endDate' => $subscription->end_date,
        ]);

        $this->sendEmail($request, $title, $message, $pdf->output(), 'subscription-receipt.pdf');

        return $request;
    }

    public function cancelRequest(int $requestId, int $adminId): UserSubscriptionRequest
    {
        $request = UserSubscriptionRequest::findOrFail($requestId);

        $request->update(['status' => 'cancelled']);

        $this->log($request, 'cancelled', 'admin', $adminId);

        $this->notify(
            $request,
            'คำขอแพ็กเกจถูกยกเลิก',
            "คำขอแพ็กเกจ {$request->plan} ของคุณถูกยกเลิกโดยเจ้าหน้าที่"
        );

        return $request;
    }

    /**
     * Auto-downgrade an expired subscription to the free BASIC plan.
     * Used by the scheduled expiry job when AUTO_FREE_MODE is enabled — no
     * UserSubscriptionRequest is involved since this is system-initiated.
     */
    public function autoDowngradeToFree(string $subscribableType, int $subscribableId): UserSubscription
    {
        UserSubscription::where('subscribable_id', $subscribableId)
            ->where('subscribable_type', $subscribableType)
            ->delete();

        $subscription = UserSubscription::create([
            'subscribable_id' => $subscribableId,
            'subscribable_type' => $subscribableType,
            'plan' => 'BASIC',
            'start_date' => Carbon::now(),
        ]);

        $userId = $subscribableType::find($subscribableId)?->user_id;

        if ($userId) {
            Notification::create([
                'user_id' => $userId,
                'title' => 'แพ็กเกจของคุณถูกปรับเป็น BASIC อัตโนมัติ',
                'message' => 'แพ็กเกจเดิมของคุณหมดอายุแล้ว ระบบได้ปรับเป็นแพ็กเกจ BASIC (ฟรี) ให้อัตโนมัติ เพื่อให้คุณยังใช้งานได้ต่อเนื่อง',
                'type' => 'SUBSCRIPTION',
                'category' => NotificationCategory::RENEWAL->value,
            ]);
        }

        return $subscription;
    }

    public function getIncomingRequests()
    {
        return UserSubscriptionRequest::where('status', 'awaiting_payment')
            ->with(['user', 'logs'])
            ->latest()
            ->get();
    }

    public function getRequestWithHistory(int $requestId): array
    {
        $request = UserSubscriptionRequest::with(['user', 'logs' => fn ($q) => $q->orderBy('created_at')])
            ->findOrFail($requestId);

        // All logs for this user across all subscription requests
        $allLogs = UserSubscriptionLog::where('user_id', $request->user_id)
            ->with('request')
            ->orderByDesc('created_at')
            ->get();

        // Current active subscription
        $currentSubscription = UserSubscription::where('subscribable_id', $request->profile_id)
            ->where('subscribable_type', $request->type)
            ->latest()
            ->first();

        // Past subscriptions (soft-deleted)
        $pastSubscriptions = UserSubscription::onlyTrashed()
            ->where('subscribable_id', $request->profile_id)
            ->where('subscribable_type', $request->type)
            ->latest()
            ->get();

        return [
            'request' => $request,
            'allLogs' => $allLogs,
            'currentSubscription' => $currentSubscription,
            'pastSubscriptions' => $pastSubscriptions,
        ];
    }

    protected function log(UserSubscriptionRequest $request, string $action, string $performedBy, ?int $adminId = null): void
    {
        UserSubscriptionLog::create([
            'subscription_request_id' => $request->id,
            'user_id' => $adminId ?? $request->user_id,
            'action' => $action,
            'performed_by' => $performedBy,
        ]);
    }

    protected function notify(UserSubscriptionRequest $request, string $title, string $message): void
    {
        Notification::create([
            'user_id' => $request->user_id,
            'title' => $title,
            'message' => $message,
            'type' => 'SUBSCRIPTION',
            'category' => NotificationCategory::RENEWAL->value,
        ]);
    }

    protected function sendEmail(
        UserSubscriptionRequest $request,
        string $title,
        string $message,
        ?string $pdfContent = null,
        ?string $pdfFilename = null
    ): void {
        if (!$request->user?->email) {
            return;
        }

        try {
            Mail::to($request->user->email)->send(
                new SubscriptionMail($title, $message, $pdfContent, $pdfFilename)
            );
        } catch (\Throwable $e) {
            \Log::error('Failed to send subscription email', [
                'subscription_request_id' => $request->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
