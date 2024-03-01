<?php

namespace App\Actions;

use App\DataTransferModels\Point;
use App\Models\Activity;
use App\Models\Gpx;
use Illuminate\Support\Facades\Storage;
use phpGPX\phpGPX;

final class RemoveInaccuracyAction
{
    /**
     * @param Point[] $points
     * @return Point[]
     */
    public function filterPoints(array $points): array
    {
        $filtered_points = [];
        foreach ($points as $point) {
            $duration = 0;
            if (isset($previous_time)) {
                $duration = abs($point->time->getTimestamp() - $previous_time->getTimestamp());
            }

            $previous_time = $point->time;

            // Filter out all points > 50km/u. This is a sports-tracker and i'm not that fast.
            $speed = $duration ? round($point->difference / $duration * 3.6, 2) : 0;
            if ($speed <= 50) {
                $filtered_points[] = $point;
            }
        }

        return $filtered_points;
    }

    public function __invoke(Activity $activity): Activity
    {
        if (!$activity->gpx instanceof Gpx) {
            throw new \InvalidArgumentException('Activity doesn\'t have a GPX file yet');
        }

        libxml_use_internal_errors(true);

        $gpx_file = phpGPX::load(Storage::path($activity->gpx->file));

        foreach ($gpx_file->tracks as $track) {
            foreach ($track->segments as $segment) {
                $segment->points = $this->filterPoints($segment->points);
            }
        }

        $temp = tempnam(sys_get_temp_dir(), 'tracktive_');
        $gpx_file->save($temp, phpGPX::XML_FORMAT);

        (new CreateGpxAction)($activity, new \SplFileInfo($temp));

        return $activity;
    }
}
