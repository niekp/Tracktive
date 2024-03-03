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
	private const NO_MOVEMENT_TIMEOUT = (60 * 10);

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
		$reasons = [];
		while (
			($part = $this->shiftTillGap($part['remaining'] ?? $points))
		) {
			if (count($part['slice']) > 1) {
				$slices[] = $part['slice'];
				$reasons[] = $part['reason'];
			}

			if (!$part['remaining']) {
				break;
			}
		}

		$activities = [];
		foreach ($slices as $key => $slice) {
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
			$activity->reason = $reasons[$key];
			$activities[] = $activity;

		}

		return array_reverse($activities);
	}

	private function shiftTillGap(array $points): array
	{
		$gathered = [];
		$reason = '';

		$previous_timestamp = 0;

		foreach ($points as $key => $point) {
			$speed = $point['speed'] * 3.6;
			$previous_point = $points[$key - 1] ?? null;
			$average_speed = $this->getAverage($gathered);
			$upcoming_average = $this->getAverage(array_slice($points, $key, 10));

			if ( // No movement in 10 minutes.
				$previous_timestamp > 0
				&& $point['timestamp'] - $previous_timestamp >= self::NO_MOVEMENT_TIMEOUT
			) {
				$reason = 'no movement';
				break;
			}

			// Too much distance between points
			if (
				$previous_point
				&& $this->distance(
					$previous_point['lat'],
					$previous_point['lon'],
					$point['lat'],
					$point['lon']
				) > 0.5
			) {
				$reason = 'big jump';
				break;
			}

			if ( // Did the average speed change by 5km/h
				$average_speed
				&& abs($speed - $average_speed) >= 5
				&& $upcoming_average !== false // Do we keep moving in the future.
				&& (abs($average_speed - $upcoming_average) >= 5 || $upcoming_average < 1) // And is the future average also different or 0
			) {
				$reason = 'changed by ' . round(abs($speed - $average_speed)) . 'kmh';
				break;
			}

			if ( // Was moving, isn't moving in the future
				$average_speed >= 1
				&& $upcoming_average !== false
				&& $upcoming_average < 1
			) {
				$reason = 'stop in future';
				break;
			}

			$gathered[] = $point;

			$previous_timestamp = $point['timestamp'];
		}

		// Filter inaccurate locations.
		$slice = array_values(array_filter($gathered, function (array $data) {
			return $data['accuracy'] <= 25;
		}));

		return [
			'average' => $average_speed,
			'reason' => $reason,
			'slice' => $slice,
			'remaining' => array_slice($points, count($gathered)),
		];
	}

	private function distance($lat1, $lon1, $lat2, $lon2)
	{
		if (($lat1 == $lat2) && ($lon1 == $lon2)) {
			return 0;
		} else {
			$theta = $lon1 - $lon2;
			$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
			$dist = acos($dist);
			$dist = rad2deg($dist);
			$miles = $dist * 60 * 1.1515;

			return ($miles * 1.609344);
		}
	}

	private function getAverage(array $points)
	{
		if (!$points) {
			return false;
		}

		$previous = 0;
		foreach ($points as $point) {
			if ($previous && $point['timestamp'] - $previous >= self::NO_MOVEMENT_TIMEOUT) {
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
			$this->phone_track_url . '?limit=600',
		);

		$data = json_decode($response->getBody()->getContents(), true);
		if (!$data) {
			return [];
		}
		$session_id = array_keys($data)[0];

		return $data[$session_id][$this->phone_track_session]['points'];
	}
}
