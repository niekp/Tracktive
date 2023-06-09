<?php

namespace App\Actions;


use App\DataTransferModels\NtfyData;
use App\Events\GpxUploaded;
use App\Models\Activity;
use Illuminate\Support\Facades\Storage;

final class ProcessNtfyAction
{
    private SendNtfyAction $send_action;

    public function __construct(
        SendNtfyAction $ntfy_action
    ) {
        $this->send_action = $ntfy_action;
    }

    public function __invoke(NtfyData $message): void
    {
        if ($message->event !== 'message'
            || ($message->tags && in_array(SendNtfyAction::TAG, $message->tags, true))
        ) {
            return;
        }

        if ($message->attachment) {
            if ($message->attachment->type !== 'application/gpx+xml') {
                ($this->send_action)('Alleen GPX bestanden worden ondersteund');
                return;
            }
        } else {
            ($this->send_action)('Stuur een GPX bestand om deze te laten verwerken');
            return;
        }

        $path = Storage::path('download/') . $message->attachment->name;
        file_put_contents($path, fopen($message->attachment->url, 'rb'));

        GpxUploaded::dispatch($path);
        $activity = Activity::query()->orderByDesc('id')->first();
        ($this->send_action)('Activiteit gemaakt.', actions: [
            'view, Bekijken, ' . route('activities.edit', $activity->id)
        ]);
    }
}
