<?php

namespace App\Console\Commands;

use App\Actions\ProcessActivityStatsAction;
use App\Models\Activity;
use Illuminate\Console\Command;

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
            $this->info("Processing activity $activity->id");

            (new ProcessActivityStatsAction)($activity);
        }

        return Command::SUCCESS;
    }
}
