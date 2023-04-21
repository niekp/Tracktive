<?php

namespace App\Jobs;

use App\Actions\ProcessNtfyAction;
use App\Actions\SendNtfyAction;
use App\DataTransferModels\NtfyData;
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

    private ProcessNtfyAction $process_action;

    /**
     * Execute the job.
     */
    public function handle(Logger $logger, SendNtfyAction $send_ntfy, ProcessNtfyAction $process_action): void
    {
        $this->logger = $logger;
        $this->process_action = $process_action;

        ($send_ntfy)('Start process');

        if (!Config::get('ntfy.url')) {
            return;
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, Config::get('ntfy.url') . '/json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $str) {
            try {

                if (curl_errno($ch)) {
                    return 0;
                }

                if (str_contains($str, 'kill')) {
                    return 0;
                }

                $this->processMessage($str);
            } catch (\Exception $e) {
                return 0;
            }

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

        ($send_ntfy)('Ntfy process killed');
    }

    private function processMessage(string $message): void
    {
        try {
            $data = NtfyData::from(json_decode($message));
            ($this->process_action)($data);
        } catch (\Exception $e) {
        }
    }
}
