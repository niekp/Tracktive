<?php

namespace App\Services;

use App\DataTransferModels\ActivityData;
use App\DataTransferModels\Point as TracktivePoint;
use phpGPX\Models\GpxFile;
use phpGPX\Models\Metadata;
use phpGPX\Models\Point;
use phpGPX\Models\Segment;
use phpGPX\Models\Track;
use Spatie\LaravelData\DataCollection;

final class GpxService
{
	public function create(ActivityData $data, DataCollection $points): GpxFile
	{
		$gpx_file = new GpxFile();

		$gpx_file->metadata = new Metadata();
		$gpx_file->metadata->time = $data->start;

		$track = new Track();

		$segment = new Segment();

		$segment->points = array_map(function (TracktivePoint $data_point): Point {
			$point = new Point(Point::TRACKPOINT);
			$point->latitude = $data_point->latitude;
			$point->longitude = $data_point->longitude;
			$point->elevation = $data_point->altitude;
			$point->time = $data_point->time;

			return $point;
		}, iterator_to_array($points));

		// Add segment to segment array of track
		$track->segments[] = $segment;

		// Recalculate stats based on received data
		$track->recalculateStats();

		// Add track to file
		$gpx_file->tracks[] = $track;

		return $gpx_file;
	}

	public function getDistance(GpxFile $gpx_file): float
	{
		$distance = 0;
		foreach ($gpx_file->tracks as $track) {
			foreach ($track->segments as $segment) {
				foreach ($segment->getPoints() as $point) {
					$distance += $point->difference;
				}
			}
		}

		return round($distance / 1000, 2);
	}
}
