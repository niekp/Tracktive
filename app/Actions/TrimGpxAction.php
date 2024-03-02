<?php

namespace App\Actions;

use App\DataTransferModels\Point;
use App\Models\Activity;
use App\Models\Gpx;
use Illuminate\Support\Facades\Storage;
use phpGPX\phpGPX;

final class TrimGpxAction
{
    /**
     * @param Point[] $points
     * @return Point[]
     */
    public function filterPoints(array $points): array
    {
        $filtered_points = [];
        foreach ($points as $key => $point) {
            $duration = 0;
            if (isset($previous_time)) {
                $duration = abs($point->time->getTimestamp() - $previous_time->getTimestamp());
            }

            $previous_time = $point->time;

            // >1.8km/u or already started.
            if (($duration && $point->difference / $duration > 0.5) || $filtered_points) {
                // Add previous point when the first movement is detected.
                if (!$filtered_points && isset($points[$key - 1])) {
                    $filtered_points[] = $points[$key - 1];
                }
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
                $segment->points = array_values($this->filterPoints($segment->points));
                $segment->points = array_values(array_reverse(
                    $this->filterPoints(array_reverse($segment->points))
                ));
            }
        }

        $temp = tempnam(sys_get_temp_dir(), 'tracktive_');
        $gpx_file->save($temp, phpGPX::XML_FORMAT);

        (new CreateGpxAction)($activity, new \SplFileInfo($temp));

        return $activity;
    }
}
