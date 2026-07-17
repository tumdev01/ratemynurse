<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use App\Services\UserSubmitRenewalService;
use Illuminate\Console\Command;

class AutoRenewExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-renew-expired-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downgrade expired subscriptions to the free BASIC plan (gated by BYPASSED_MODE / AUTO_FREE_MODE)';

    public function __construct(protected UserSubmitRenewalService $service)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (config('subscription.bypassed_mode')) {
            $this->info('Bypassed mode is on — skipping, no action taken.');
            return;
        }

        if (!config('subscription.auto_free_mode')) {
            $this->info('Auto free mode is off — nothing to do.');
            return;
        }

        $expired = UserSubscription::where('plan', '!=', 'BASIC')
            ->where('start_date', '<', now()->subMonth())
            ->get();

        foreach ($expired as $subscription) {
            $this->service->autoDowngradeToFree($subscription->subscribable_type, $subscription->subscribable_id);
        }

        $this->info("{$expired->count()} subscription(s) auto-downgraded to BASIC.");
    }
}
