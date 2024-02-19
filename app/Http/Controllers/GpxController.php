<?php

namespace App\Http\Controllers;

use App\Events\GpxUploaded;
use App\Models\Activity;
use App\Services\GpxService;
use App\Services\PhoneTrackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use phpGPX\Models\Point;

final class GpxController extends Controller
{
    public function index(PhoneTrackService $service)
    {
        return view('gpx.index', [
            'activities' => $service->fetchActivities(),
        ]);
    }

    public function show(
        int $index,
        PhoneTrackService $service,
        GpxService $gpx_service,
    ) {
        $activity = $service->fetchActivities()[$index];
        $this->calculateStats($activity, $gpx_service);

        return view('gpx.show', [
            'activity' => $activity,
            'stats' => $activity->getData(),
            'index' => $index,
        ]);
    }

    private function calculateStats(Activity $activity, GpxService $gpx_service)
    {
        $gpx_file = $gpx_service->create($activity->getData(), $activity->getPoints());

        $distance = 0;
        foreach ($gpx_file->tracks as $track) {
            foreach ($track->segments as $segment) {
                foreach ($segment->getPoints() as $point) {
                    $distance += $point->difference;
                }
            }
        }

        $activity->data->distance = round($distance / 1000, 2);
    }

    public function create(
        Request $request,
        PhoneTrackService $service,
        GpxService $gpx_service,
    ) {
        // Fetch activity
        $index = $request->get('index');
        $activity = $service->fetchActivities()[$index];

        // Build GPX
        $file = $gpx_service->create($activity->getData(), $activity->getPoints());
        $filename = tempnam(sys_get_temp_dir(), 'gpx');
        $file->save($filename, \phpGPX\phpGPX::XML_FORMAT);


        // Trigger GpxUpload to import data.
        GpxUploaded::dispatch($filename);
        $activity = Activity::query()->orderByDesc('id')->first();

        return Redirect::route('activities.edit', $activity->id);
    }
}
