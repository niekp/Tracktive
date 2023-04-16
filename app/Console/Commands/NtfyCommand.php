<?php

namespace App\Console\Commands;

use App\Jobs\ProcessNtfy;
use Illuminate\Console\Command;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Config;

class NtfyCommand extends Command
{
    private Logger $logger;

    public function __construct(
        Logger $logger
    ) {
        parent::__construct();
        $this->logger = $logger;
    }

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'app:ntfy';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Monitor ntfy';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ProcessNtfy::dispatch();
        return Command::SUCCESS;
    }
}
