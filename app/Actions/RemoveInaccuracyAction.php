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

		// Get speeds.
		$speeds = [];
		foreach ($points as $point) {
			$duration = 0;
			if (isset($previous_time)) {
				$duration = abs(
					$point->time->getTimestamp() - $previous_time->getTimestamp()
				);
			}

			$previous_time = $point->time;

			$speed = $duration ? round($point->difference / $duration * 3.6, 2) : 0;
			$speeds[] = $speed;
		}

		$magnitude = 2.5;
		$count = count($speeds);
		$mean = array_sum($speeds) / $count; // Calculate the mean
		$deviation = sqrt(
			array_sum(
				array_map(
					static function ($x, $mean) {
						return pow($x - $mean, 2);
					},
					$speeds,
					array_fill(0, $count, $mean)
				)
			) / $count
		) * $magnitude; // Calculate standard deviation and times by magnitude

		$max_speed = $mean + $deviation;

		foreach ($points as $point) {
            $duration = 0;
            if (isset($previous_time)) {
                $duration = abs($point->time->getTimestamp() - $previous_time->getTimestamp());
            }

            $previous_time = $point->time;

            // Filter out all odd speeds.
            $speed = $duration ? round($point->difference / $duration * 3.6, 2) : 0;
            if ($speed <= $max_speed) {
                $filtered_points[] = $point;
			}
        }

        return $filtered_points;
    }

    public function cleanup(Activity $activity, int $passes = 1): Activity
    {
        if (!$activity->gpx instanceof Gpx) {
            throw new \InvalidArgumentException('Activity doesn\'t have a GPX file yet');
        }

        libxml_use_internal_errors(true);

		$content = null;

		for ($pass = 1; $pass <= $passes; $pass++) {
			$gpx_file = $content
				? phpGPX::parse($content)
				: phpGPX::load(Storage::path($activity->gpx->file));

			foreach ($gpx_file->tracks as $track) {
				foreach ($track->segments as $segment) {
					// Pass the points through the filter twice.
					$segment->points = $this->filterPoints($segment->points);
				}
			}

			$document = $gpx_file->toXML();
			$content = $document->saveXML($document);
		}

        $temp = tempnam(sys_get_temp_dir(), 'tracktive_');
        $gpx_file->save($temp, phpGPX::XML_FORMAT);

        (new CreateGpxAction)($activity, new \SplFileInfo($temp));

        return $activity;
    }

	public function __invoke(Activity $activity): Activity
	{
		$activity = $this->cleanup($activity, 3);

		return $activity;
	}
}
