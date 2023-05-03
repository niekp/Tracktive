<?php

namespace App\Actions;

use App\DataTransferModels\ActivityData;
use App\DataTransferModels\Point;
use App\Models\Activity;
use App\Models\Gpx;
use DateTime;
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
        $points = [];

        foreach ($gpx_file->tracks as $track) {
            foreach ($track->segments as $segment) {
                foreach ($segment->getPoints() as $point) {
                    if (!isset($data->start)) {
                        $data->start = $point->time;
                    }

                    $data->stop = $point->time;

                    $data->distance += $point->difference ?? 0.0;

                    $duration = 0;
                    if ($previous_time instanceof DateTime) {
                        $duration = $point->time->getTimestamp() - $previous_time->getTimestamp();
                    }

                    $active = $duration && $point->difference / $duration > 0.5; // 1.8km/u

                    if ($active) {
                        $data->seconds_active += $duration;
                    } else {
                        $data->seconds_paused += $duration;
                    }

                    $points[] = new Point(
                        $point->latitude,
                        $point->longitude,
                        $point->time,
                        $active,
                        $duration ? round($point->difference / $duration * 3.6, 2) : 0,
                    );

                    $previous_time = $point->time;
                }
            }
        }

        $data->points = new DataCollection(Point::class, $points);
        $data->average_speed_active = round($data->distance / $data->seconds_active * 3.6, 2);
        $data->average_speed_total = round($data->distance / ($data->seconds_active + $data->seconds_paused) * 3.6, 2);
        $data->distance = round($data->distance / 1000, 2);

        $activity->data = $data;
        $activity->date = $data->start;
        $activity->save();

        return $activity;
    }
}
