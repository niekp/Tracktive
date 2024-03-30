<?php

namespace App\Console\Commands;

use App\Actions\SendNtfyAction;
use App\Events\GpxUploaded;
use App\Models\Activity;
use App\Services\GpxService;
use App\Services\PhoneTrackService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

final class ImportPhoneTrackCommand extends Command
{
	protected $signature = 'app:import-phonetrack';

	public function __construct(
		private PhoneTrackService $phone_track,
		private GpxService $gpx_service,
		private SendNtfyAction $ntfy,
	) {
		parent::__construct();
	}

	public function __invoke()
	{
		$activities = array_filter(
			$this->phone_track->fetchActivities(),
			function (Activity $activity) {
				if ($activity->getData()->start < Carbon::today()) {
					return false;
				}

				if ($activity->getData()->stop > Carbon::now()->subMinutes(10)) {
					return false;
				}

				if (
					Activity::query()
					->where('date', '>=', $activity->date->subMinutes(10))
					->where('date', '<=', $activity->date->addMinutes(10))
					->exists()
				) {
					return false;
				}

				$distance = $this->gpx_service->getDistance(
					$this->gpx_service->create($activity->getData(), $activity->getPoints()),
				);

				if ($distance < 1.5) {
					return false;
				}

				return true;
			}
		);
		
		foreach ($activities as $activity) {
			// Build GPX
			$file = $this->gpx_service->create($activity->getData(), $activity->getPoints());
			$filename = tempnam(sys_get_temp_dir(), 'gpx');
			$file->save($filename, \phpGPX\phpGPX::XML_FORMAT);

			// Trigger GpxUpload to import data.
			GpxUploaded::dispatch($filename);
			/** @var Activity $created_activity */
			$created_activity = Activity::query()->orderByDesc('id')->first();

			($this->ntfy)(
				sprintf(
					"Ha topper! Wil je je activiteit opslaan?\n\nAfstand: %.02f\nGemiddelde snelheid: %.02f\nTijdsduur: %s",
					$created_activity->getData()->distance,
					$created_activity->getData()->average_speed_total,
					gmdate('H:i:s', $created_activity->getData()->seconds_active + $created_activity->getData()->seconds_paused)
				),
				null,
				[
					'view, Importeren, ' . URL::route('activities.edit', $created_activity->id),
				]
			);
		}
	}
}
