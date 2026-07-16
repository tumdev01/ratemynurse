<?php

namespace Database\Seeders;

use App\Enums\ActionType;
use App\Models\ActionStat;
use App\Models\ActionStatSummary;
use App\Models\Member;
use App\Models\Nursing;
use App\Models\NursingHome;
use App\Models\NursingHomeProfile;
use App\Models\NursingProfile;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ActionStatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Action Stats...');

        // Get existing profiles or create sample IDs
        $nursingProfileIds = NursingProfile::pluck('id')->take(3)->toArray();
        $nursingHomeProfileIds = NursingHomeProfile::pluck('id')->take(3)->toArray();

        // If no profiles exist, use sample IDs (1, 2, 3)
        if (empty($nursingProfileIds)) {
            $nursingProfileIds = [1, 2, 3];
            $this->command->warn('No NursingProfile found, using sample IDs: 1, 2, 3');
        }

        if (empty($nursingHomeProfileIds)) {
            $nursingHomeProfileIds = [1, 2, 3];
            $this->command->warn('No NursingHomeProfile found, using sample IDs: 1, 2, 3');
        }

        // Get sample actors (Members)
        $memberIds = Member::pluck('id')->take(5)->toArray();
        if (empty($memberIds)) {
            $memberIds = [1, 2, 3, 4, 5];
            $this->command->warn('No Members found, using sample IDs: 1, 2, 3, 4, 5');
        }

        $actions = ActionType::values();

        // Generate data for the last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        $this->command->info('Generating action stats from ' . $startDate->toDateString() . ' to ' . $endDate->toDateString());

        // Clear existing data
        ActionStat::truncate();
        ActionStatSummary::truncate();

        $totalStats = 0;

        // Generate for NursingProfile subjects
        foreach ($nursingProfileIds as $profileId) {
            $this->command->info("Generating stats for NursingProfile ID: $profileId");

            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                foreach ($actions as $action) {
                    // Random count between 5-50 for each action per day
                    $count = rand(5, 50);

                    // Create summary record
                    ActionStatSummary::create([
                        'action' => $action,
                        'subject_id' => $profileId,
                        'subject_type' => 'App\\Models\\NursingProfile',
                        'date' => $currentDate->toDateString(),
                        'count' => $count,
                    ]);

                    // Create some raw action logs (just a few samples, not all)
                    $sampleCount = min($count, 3);
                    for ($i = 0; $i < $sampleCount; $i++) {
                        $actorId = $memberIds[array_rand($memberIds)];
                        ActionStat::create([
                            'actor_id' => $actorId,
                            'actor_type' => 'App\\Models\\Member',
                            'action' => $action,
                            'subject_id' => $profileId,
                            'subject_type' => 'App\\Models\\NursingProfile',
                            'metadata' => json_encode(['source' => 'seeder', 'sample' => true]),
                            'ip_address' => '127.0.0.1',
                            'user_agent' => 'Seeder/1.0',
                            'created_at' => $currentDate->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59)),
                        ]);
                        $totalStats++;
                    }
                }
                $currentDate->addDay();
            }
        }

        // Generate for NursingHomeProfile subjects
        foreach ($nursingHomeProfileIds as $profileId) {
            $this->command->info("Generating stats for NursingHomeProfile ID: $profileId");

            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                foreach ($actions as $action) {
                    // Random count between 10-80 for each action per day (nursing homes may have more views)
                    $count = rand(10, 80);

                    // Create summary record
                    ActionStatSummary::create([
                        'action' => $action,
                        'subject_id' => $profileId,
                        'subject_type' => 'App\\Models\\NursingHomeProfile',
                        'date' => $currentDate->toDateString(),
                        'count' => $count,
                    ]);

                    // Create some raw action logs (just a few samples)
                    $sampleCount = min($count, 3);
                    for ($i = 0; $i < $sampleCount; $i++) {
                        $actorId = $memberIds[array_rand($memberIds)];
                        ActionStat::create([
                            'actor_id' => $actorId,
                            'actor_type' => 'App\\Models\\Member',
                            'action' => $action,
                            'subject_id' => $profileId,
                            'subject_type' => 'App\\Models\\NursingHomeProfile',
                            'metadata' => json_encode(['source' => 'seeder', 'sample' => true]),
                            'ip_address' => '127.0.0.1',
                            'user_agent' => 'Seeder/1.0',
                            'created_at' => $currentDate->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59)),
                        ]);
                        $totalStats++;
                    }
                }
                $currentDate->addDay();
            }
        }

        $summaryCount = ActionStatSummary::count();
        $this->command->info("Seeding complete!");
        $this->command->info("Total ActionStat records: $totalStats");
        $this->command->info("Total ActionStatSummary records: $summaryCount");

        // Display sample data summary
        $this->command->newLine();
        $this->command->info('=== Sample Data Summary ===');

        $this->command->info('NursingProfile Stats (Today):');
        foreach ($nursingProfileIds as $profileId) {
            $todayStats = ActionStatSummary::where('subject_id', $profileId)
                ->where('subject_type', 'App\\Models\\NursingProfile')
                ->where('date', Carbon::today()->toDateString())
                ->get();

            $this->command->line("  Profile ID $profileId:");
            foreach ($todayStats as $stat) {
                $this->command->line("    - {$stat->action}: {$stat->count}");
            }
        }

        $this->command->newLine();
        $this->command->info('NursingHomeProfile Stats (Today):');
        foreach ($nursingHomeProfileIds as $profileId) {
            $todayStats = ActionStatSummary::where('subject_id', $profileId)
                ->where('subject_type', 'App\\Models\\NursingHomeProfile')
                ->where('date', Carbon::today()->toDateString())
                ->get();

            $this->command->line("  Profile ID $profileId:");
            foreach ($todayStats as $stat) {
                $this->command->line("    - {$stat->action}: {$stat->count}");
            }
        }
    }
}
