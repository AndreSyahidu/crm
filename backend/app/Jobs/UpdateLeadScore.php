<?php

namespace App\Jobs;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateLeadScore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $leadId;

    public function __construct($leadId)
    {
        $this->leadId = $leadId;
    }

    public function handle()
    {
        $lead = Lead::find($this->leadId);

        if ($lead) {
            $lead->updateLeadScore();
        }
    }
}
