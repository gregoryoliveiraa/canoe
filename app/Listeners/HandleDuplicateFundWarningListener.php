<?php

namespace App\Listeners;

use App\Events\DuplicateFundWarningEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleDuplicateFundWarningListener
{
    /**
     * Handle the DuplicateFundWarningEvent event.
     *
     * @param DuplicateFundWarningEvent $event
     * @return void
     */
    public function handle(DuplicateFundWarningEvent $event): void
    {
        if ($event->result['isDuplicateFund'] ?? false) {
            Log::warning('Create Fund | Duplicate fund detected!', [
                'fund_name'       => $event->fundName,
                'manager_name'    => $event->managerName,
                'match_fund_name' => $event->result['matchFundName'],
                'match_alias'     => $event->result['matchAlias'],
            ]);
        }
    }
}
