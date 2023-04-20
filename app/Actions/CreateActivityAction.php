<?php

namespace App\Actions;

use App\Models\Activity;
use Carbon\Carbon;

final class CreateActivityAction
{
    public function __invoke(): Activity
    {
        /** @var Activity $activity */
        $activity = Activity::create([
            'date' => Carbon::now()
        ]);

        return $activity;
    }
}
