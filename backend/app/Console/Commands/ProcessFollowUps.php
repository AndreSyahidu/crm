<?php

namespace App\Console\Commands;

use App\Jobs\ProcessFollowUpSequence;
use Illuminate\Console\Command;

class ProcessFollowUps extends Command
{
    protected $signature = 'crm:process-followups';
    protected $description = 'Process due follow-up sequences';

    public function handle()
    {
        $this->info('Processing follow-up sequences...');

        ProcessFollowUpSequence::dispatch();

        $this->info('Follow-up job dispatched!');
    }
}
