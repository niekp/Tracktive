<?php

namespace App\Http\Controllers;

use App\Events\GpxUploaded;
use App\Models\Activity;
use App\Services\GpxService;
use App\Services\PhoneTrackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

final class GpxController extends Controller
{
    public function index(PhoneTrackService $service)
    {
        return view('gpx.index', [
            'activities' => $service->fetchActivities(),
        ]);
    }

    public function show(int $index, PhoneTrackService $service)
    {
        $activity = $service->fetchActivities()[$index];
        return view('gpx.show', [
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

        // Trigger GpxUpload to import data.
        GpxUploaded::dispatch($file);
        $activity = Activity::query()->orderByDesc('id')->first();

        return Redirect::route('activities.edit', $activity->id);
    }
}
