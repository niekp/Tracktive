<?php

namespace App\Services;

use App\DataTransferModels\ActivityData;
use App\DataTransferModels\Point;
use App\Models\Activity;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Spatie\LaravelData\DataCollection;

final class PhoneTrackService
{
	public function __construct(
		private Client $client,
		private ?string $phone_track_url = null,
		private ?string $phone_track_session = null,
	) {
		$this->phone_track_url ??= Config::get('phone_track.url');
		$this->phone_track_session ??= Config::get('phone_track.session');
	}

	public function fetchActivities()
	{
		$points = $this->getPoints();

		$slices = [];
		while (
			($part = $this->shiftTillGap($part['remaining'] ?? $points))
		) {
			if (count($part['slice']) > 1) {
				$slices[] = $part['slice'];
			}

			if (!$part['remaining']) {
				break;
			}
		}

		$activities = [];
		foreach ($slices as $slice) {
			$activity = new Activity();
			$activity->date = Carbon::createFromTimestamp($slice[0]['timestamp']);

			$points = array_map(function (array $data): Point {
				return new Point(
					$data['lat'],
					$data['lon'],
					Carbon::createFromTimestamp($data['timestamp']),
					true,
					($data['speed'] ?? 0) * 3.6,
					null,
					null,
					$data['altitude'],
				);
			}, $slice);

			$speed = round(array_sum(array_map(fn (Point $point) => $point->speed, $points)) / count($points), 2);

			if ($speed > 20 || count($points) < 5) {
				continue;
			}

			$activity->points = new DataCollection(Point::class, $points);
			$activity->data = new ActivityData();
			$activity->data->start = reset($points)->time;
			$activity->data->stop = end($points)->time;
			$activity->data->average_speed_total = $speed;
			$activity->image = true;
			$activities[] = $activity;
		}

		return array_reverse($activities);
	}

	private function shiftTillGap(array $points): array
	{
		$gathered = [];

		$previous_timestamp = 0;

		foreach ($points as $key => $point) {
			$speed = $point['speed'] * 3.6;
			$average_speed = $this->getAverage($gathered);
			$upcoming_average = $this->getAverage(array_slice($points, $key, 10));

			if (
				( // No movement in 2 minutes.
					$previous_timestamp > 0
					&& $point['timestamp'] - $previous_timestamp > 120
				)
				|| ( // Diff in speed of > km/h to break the parts.
					($average_speed && abs($speed - $average_speed) >= 5)
					&& (!$upcoming_average || abs($speed - $upcoming_average) >= 5)
				)
				|| ( // Was moving, isn't moving in the future
					$average_speed >= 1
					&& $upcoming_average !== false
					&& $upcoming_average < 1
				)
			) {
				break;
			}

			$gathered[] = $point;

			$previous_timestamp = $point['timestamp'];
		}

		return [
			'average' => $average_speed,
			'slice' => array_slice($gathered, 0, count($gathered)),
			'remaining' => array_slice($points, count($gathered) + 1),
		];
	}

	private function getAverage(array $points)
	{
		if (!$points) {
			return false;
		}

		$previous = 0;
		foreach ($points as $point) {
			if ($previous && $point['timestamp'] - $previous > 120) {
				return false;
			}
			$previous = $point['timestamp'];
		}

		return array_sum(array_map(fn ($point) => $point['speed'] * 3.6, $points)) / count($points);
	}

	private function getPoints()
	{
		$response = $this->client->request(
			'GET',
			$this->phone_track_url . '?limit=500',
		);

		$data = json_decode($response->getBody()->getContents(), true);
		if (!$data) {
			return [];
		}
		$session_id = array_keys($data)[0];

		return $data[$session_id][$this->phone_track_session]['points'];
	}
}
