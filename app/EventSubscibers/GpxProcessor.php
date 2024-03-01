<?php

namespace App\EventSubscibers;

use App\Actions\CreateActivityAction;
use App\Actions\CreateGpxAction;
use App\Actions\ProcessActivityStatsAction;
use App\Actions\RemoveInaccuracyAction;
use App\Actions\TrimGpxAction;
use App\Events\GpxUploaded;
use Illuminate\Events\Dispatcher;

final class GpxProcessor
{
    public function handleGpxUploaded(GpxUploaded $event)
    {
        $activity = (new CreateActivityAction)();
        (new CreateGpxAction)($activity, $event->path);
        (new RemoveInaccuracyAction)($activity);
        (new TrimGpxAction)($activity);
        (new ProcessActivityStatsAction)($activity);
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(
            GpxUploaded::class,
            [self::class, 'handleGpxUploaded']
        );
    }
}
