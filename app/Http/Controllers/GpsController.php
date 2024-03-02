<?php

namespace App\Http\Controllers;

use App\Events\GpxUploaded;
use App\Models\Activity;
use App\Services\GpxService;
use App\Services\PhoneTrackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

final class GpsController extends Controller
{
    public function index(PhoneTrackService $service)
    {
        return view('gps.index', [
            'activities' => $service->fetchActivities(),
        ]);
    }

    public function show(
        int $index,
        PhoneTrackService $service,
        GpxService $gpx_service,
    ) {
        $activity = $service->fetchActivities()[$index];
        $activity->data->distance = $gpx_service->getDistance(
            $gpx_service->create($activity->getData(), $activity->getPoints()),
        );

        return view('gps.show', [
            'activity' => $activity,
            'stats' => $activity->getData(),
            'index' => $index,
        ]);
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

    public function download(
        int $index,
        PhoneTrackService $service,
        GpxService $gpx_service,
    ) {
        $activity = $service->fetchActivities()[$index];

        // Build GPX
        $file = $gpx_service->create($activity->getData(), $activity->getPoints());
        $filename = tempnam(sys_get_temp_dir(), 'gpx');
        $file->save($filename, \phpGPX\phpGPX::XML_FORMAT);

        return response()->download($filename);
    }
}
