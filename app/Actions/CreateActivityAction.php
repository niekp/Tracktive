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

        (new CreateGpxAction)($activity, $data->file);

        return $activity;
    }
}
