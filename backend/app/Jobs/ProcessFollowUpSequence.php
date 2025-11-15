<?php

namespace App\Jobs;

use App\Models\FollowUpEnrollment;
use App\Models\MessageQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessFollowUpSequence implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $dueEnrollments = FollowUpEnrollment::dueForAction()->get();

        Log::info("Processing {$dueEnrollments->count()} follow-up enrollments");

        foreach ($dueEnrollments as $enrollment) {
            try {
                $enrollment->processNextStep();
                Log::info("Processed follow-up step for enrollment #{$enrollment->id}");
            } catch (\Exception $e) {
                Log::error("Failed to process enrollment #{$enrollment->id}: " . $e->getMessage());
            }
        }
    }
}
