<?php

namespace App\Actions;

use Illuminate\Support\Facades\Config;

final class SendNtfyAction
{
    public const TAG = 'man_playing_handball';

    public function __invoke(string $message, ?string $url = null, array $actions = [])
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, Config::get('ntfy.url'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);

        $headers = [
            'Content-Type: text/plain',
            'Authorization: Bearer ' . Config::get('ntfy.token'),
            'Tags: ' . self::TAG,
        ];

        if ($url) {
            $headers[] = 'Click: ' . $url;
        }

        if ($actions) {
            $headers[] = 'Actions: ' . implode(';', $actions);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_exec($ch);

        curl_close($ch);
    }

}
