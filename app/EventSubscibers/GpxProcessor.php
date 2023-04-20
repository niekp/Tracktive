<?php

namespace App\EventSubscibers;

use App\Actions\CreateActivityAction;
use App\Actions\CreateGpxAction;
use App\Actions\ProcessActivityStatsAction;
use App\Events\ActivityCreated;
use App\Events\GpxUploaded;
use Illuminate\Events\Dispatcher;

final class GpxProcessor
{
    public function handleGpxUploaded(GpxUploaded $event)
    {
        $activity = (new CreateActivityAction)();
        (new CreateGpxAction)($activity, $event->file);
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
