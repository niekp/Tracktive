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
					$data['speed'] ?? 0,
					null,
					null,
					$data['altitude'],
				);
			}, $slice);

			$activity->points = new DataCollection(Point::class, $points);
			$activity->data = new ActivityData();
			$activity->data->start = reset($points)->time;
			$activity->data->stop = end($points)->time;
			$activity->image = true;
			$activities[] = $activity;
		}

		return array_reverse($activities);
	}

	private function shiftTillGap(array $points): array
	{
		$gathered = [];

		$previous_timestamp = 0;
		$last_movement = 0;
		$previous_speed = 0;
		$speeds = [];

		foreach ($points as $point) {
			$average_speed = $speeds ? array_sum($speeds) / count($speeds) : 0;
			if (
				(
					$previous_timestamp > 0
					&& $point['timestamp'] - $previous_timestamp > 120
				)
				|| (
					$last_movement > 0
					&& $point['timestamp'] - $last_movement > 120
				)
				|| (
					$previous_speed > 25
					&& $point['speed'] < 8
				)
				|| (
					$average_speed < 7
					&& $point['speed'] >= 7
					&& count($gathered) > 5
				)
			) {
				break;
			}

			$gathered[] = $point;

			$previous_timestamp = $point['timestamp'];
			$previous_speed = $point['speed'];
			$speeds[] = $point['speed'];
			if ($point['speed'] > 2) {
				$last_movement = $previous_timestamp;
			}
		}

		$gathered = array_slice($points, 0, count($gathered));

		return [
			'slice' => array_slice($gathered, 0, count($gathered) - 1),
			'remaining' => array_slice($points, count($gathered)),
		];
	}

	private function getPoints()
	{
		$response = $this->client->request(
			'GET',
			$this->phone_track_url . '?limit=400',
		);

		$data = json_decode($response->getBody()->getContents(), true);
		if (!$data) {
			return [];
		}
		$session_id = array_keys($data)[0];

		return $data[$session_id][$this->phone_track_session]['points'];
	}
}
