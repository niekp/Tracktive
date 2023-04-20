<?php

namespace App\Actions;

use App\DataTransferModels\ActivityData;
use App\DataTransferModels\Coordinate;
use App\Models\Activity;
use App\Models\Gpx;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use phpGPX\phpGPX;
use Spatie\LaravelData\DataCollection;

final class ProcessActivityStatsAction
{
    public function __invoke(Activity $activity): Activity
    {
        if (!$activity->gpx instanceof Gpx)
        {
            throw new \InvalidArgumentException('Activity doesn\'t have a GPX file yet');
        }

        $gpx_file = phpGPX::load(Storage::path($activity->gpx->file));

        $data = new ActivityData();
        $previous_time = null;
        $coordinates = [];

        foreach ($gpx_file->tracks as $track) {
            foreach ($track->segments as $segment) {
                foreach ($segment->getPoints() as $point) {
                    $time = Carbon::createFromInterface($point->time);
                    if (!isset($data->start)) {
                        $data->start = $time;
                    }

                    $data->stop = $time;

                    $coordinates[] = new Coordinate(
                        $point->latitude,
                        $point->longitude,
                        $time,
                    );

                    $data->distance += $point->difference ?? 0.0;

                    $duration = 0;
                    if ($previous_time instanceof Carbon) {
                        $duration = $time->diffInSeconds($previous_time);
                    }

                    if ($duration && $point->difference / $duration > 0.5) { // 1.8km/u
                        $data->seconds_active += $duration;
                    } else {
                        $data->seconds_paused += $duration;
                    }

                    $previous_time = $time;
                }
            }
        }

        $data->coordinates = new DataCollection(Coordinate::class, $coordinates);
        $data->average_speed_active = round($data->distance / $data->seconds_active * 3.6, 2);
        $data->average_speed_total = round($data->distance / ($data->seconds_active + $data->seconds_paused) * 3.6, 2);
        $data->distance = round($data->distance / 1000, 2);

        $activity->data = $data;
        $activity->date = $data->start;
        $activity->save();

        return $activity;
    }
}