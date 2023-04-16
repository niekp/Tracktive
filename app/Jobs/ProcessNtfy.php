<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Log\Logger;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class ProcessNtfy implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Logger $logger;

    /**
     * Execute the job.
     */
    public function handle(Logger $logger): void
    {
        $this->logger = $logger;
        $this->sendMessage('Start process');

        if (!Config::get('ntfy.url')) {
            return;
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, Config::get('ntfy.url') . '/json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $str) {
            if (str_contains($str, 'kill')) {
                $this->logger->info('Killswitch triggered');
                return 0;
            }

            $this->processMessage($str);

            return strlen($str);
        });

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . Config::get('ntfy.token'),
        ]);

        curl_exec($ch);
        if (curl_errno($ch)) {
            $this->logger->error('Error processing ntfy', [
                'error' => curl_error($ch),
            ]);
        }

        curl_close($ch);

        $this->sendMessage('Ntfy process killed');
    }

    private function sendMessage($message): void
    {
        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, Config::get('ntfy.url'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $message);

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: text/plain',
                'Authorization: Bearer ' . Config::get('ntfy.token'),
            ]);

            curl_exec($ch);

            curl_close($ch);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), [
                'e' => $e,
            ]);
        }
    }

    private function processMessage(string $message): void
    {
        try {
            $data = json_decode($message);
            if ($data->message) {
                dump($data->message, $data);
            }
        } catch (\Exception) {
            $this->logger->error($e->getMessage(), [
                'e' => $e,
            ]);
        }
    }
}
