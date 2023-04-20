<?php

namespace App\Console\Commands;

use App\Actions\ProcessActivityStatsAction;
use App\Jobs\ProcessNtfy;
use App\Models\Activity;
use Illuminate\Console\Command;
use Illuminate\Log\Logger;

class ReprocessDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'app:reprocess-data';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Reprocess all data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (Activity::all() as $activity) {
            (new ProcessActivityStatsAction)($activity);
        }

        return Command::SUCCESS;
    }
}
