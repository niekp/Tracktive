<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Log\Logger;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class ProcessNtfy implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Execute the job.
     */
    public function handle(Logger $logger): void
    {
        if (!Config::get('ntfy.url')) {
            return;
        }

        $logger->info('start');

        $context = Config::get('ntfy.token') ? stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'Authorization: Bearer ' . Config::get('ntfy.token') . "\r\n",
            ],
        ]) : null;

        $fp = fopen(Config::get('ntfy.token') . '/json', 'rb', false, $context);
        if (!$fp) {
            die('cannot open stream');
        }

        while (!feof($fp)) {
            $logger->info(fgets($fp, 2048));
        }
        
        fclose($fp);
    }
}
