<?php

namespace App\Actions;

use App\DataTransferModels\ActivityData;
use App\Models\Activity;
use Carbon\Carbon;

final class CreateActivityAction
{
    public function __invoke(ActivityData $data): Activity
    {
        /** @var Activity $activity */
        $activity = Activity::create([
            'date' => Carbon::now()
        ]);

        $gpx = (new CreateGpxAction)($activity, $data->file);

        // TODO: Update date with GPX.

        return $activity;
    }
}
