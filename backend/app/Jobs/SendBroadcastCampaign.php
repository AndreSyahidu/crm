<?php

namespace App\Jobs;

use App\Models\BroadcastCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBroadcastCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaign;

    public function __construct(BroadcastCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function handle()
    {
        Log::info("Starting broadcast campaign #{$this->campaign->id}");

        try {
            $this->campaign->sendToRecipients();
            $this->campaign->updateStats();

            Log::info("Broadcast campaign #{$this->campaign->id} completed");
        } catch (\Exception $e) {
            Log::error("Broadcast campaign #{$this->campaign->id} failed: " . $e->getMessage());

            $this->campaign->status = 'failed';
            $this->campaign->save();
        }
    }
}
