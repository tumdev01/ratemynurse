<?php

namespace App\Console\Commands;

use App\Models\UserSubscriptionLog;
use App\Models\UserSubscriptionRequest;
use Illuminate\Console\Command;

class ExpireSubscriptionRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-subscription-requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark awaiting_payment subscription requests older than 14 days as expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $requests = UserSubscriptionRequest::where('status', 'awaiting_payment')
            ->where('created_at', '<', now()->subDays(14))
            ->get();

        foreach ($requests as $request) {
            $request->update(['status' => 'expired']);

            UserSubscriptionLog::create([
                'subscription_request_id' => $request->id,
                'user_id' => $request->user_id,
                'action' => 'expired',
                'performed_by' => 'system',
            ]);
        }

        $this->info("{$requests->count()} subscription request(s) expired.");
    }
}
