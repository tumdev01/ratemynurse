<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UserSubmitRenewalService;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function __construct(
        protected UserSubmitRenewalService $service
    ) {}

    public function dashboard()
    {
        $incomingRequests = $this->service->getIncomingRequests();

        return view('pages.subscription.dashboard', compact('incomingRequests'));
    }

    public function show(int $id)
    {
        $data = $this->service->getRequestWithHistory($id);

        return view('pages.subscription.show', [
            'request' => $data['request'],
            'allLogs' => $data['allLogs'],
            'currentSubscription' => $data['currentSubscription'],
            'pastSubscriptions' => $data['pastSubscriptions'],
        ]);
    }

    public function acceptPayment(int $id)
    {
        $this->service->acceptPayment($id, Auth::id());

        return redirect()->route('subscription.dashboard')
            ->with('success', 'Payment accepted and subscription activated.');
    }
}
