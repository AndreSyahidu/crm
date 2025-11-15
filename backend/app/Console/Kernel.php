<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\SendDailyRecap::class,
        Commands\ProcessFollowUps::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Daily recap - every day at configured time
        $recapTime = env('DAILY_RECAP_TIME', '08:00');
        $schedule->command('crm:daily-recap')->dailyAt($recapTime);

        // Process follow-ups - every 30 minutes
        $schedule->command('crm:process-followups')->everyThirtyMinutes();

        // Update lead scores - every hour
        $schedule->call(function () {
            \App\Models\Lead::chunk(100, function ($leads) {
                foreach ($leads as $lead) {
                    \App\Jobs\UpdateLeadScore::dispatch($lead->id);
                }
            });
        })->hourly();

        // Process scheduled broadcasts - every 5 minutes
        $schedule->call(function () {
            $campaigns = \App\Models\BroadcastCampaign::dueToSend()->get();
            foreach ($campaigns as $campaign) {
                $campaign->start();
                \App\Jobs\SendBroadcastCampaign::dispatch($campaign);
            }
        })->everyFiveMinutes();

        // Clean old sessions - daily
        $schedule->command('auth:clear-resets')->daily();

        // Update segment counts - every 6 hours
        $schedule->call(function () {
            \App\Models\Segment::where('is_dynamic', true)->chunk(50, function ($segments) {
                foreach ($segments as $segment) {
                    $service = new \App\Services\SegmentService();
                    $segment->cached_count = $service->calculateSegmentSize($segment->filter_rules);
                    $segment->last_calculated_at = now();
                    $segment->save();
                }
            });
        })->everySixHours();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
