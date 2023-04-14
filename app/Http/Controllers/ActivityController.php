<?php

namespace App\Http\Controllers;

use App\Actions\CreateActivityAction;
use App\DataTransferModels\ActivityData;
use App\DataTransferModels\ActivityResource;
use App\Models\Activity;
use App\Models\Gpx;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

final class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $activities = Activity::query()
            ->orderByDesc('date')
            ->get();

        return view('index', [
            'activities' => $activities,
        ]);
    }

    public function create()
    {
        return view('create');
    }

    public function show(Activity $activity)
    {
        return view('show', [
            'activity' => $activity,
            'stats' => $activity->getData(),
        ]);
    }

    public function store(Request $request)
    {
        if (!$gpx = $request->file('gpx')) {
            return Redirect::route('create');
        }

        $activity = (new CreateActivityAction)(new ActivityData([
            'file' => $gpx,
        ]));

        return Redirect::route('activities.show', $activity->id);
    }

    public function destroy(Activity $activity)
    {
        /** @var Gpx $gpx */
        foreach ($activity->gpxes() as $gpx) {
            Storage::delete($gpx->file);
        }

        $activity->delete();

        return Redirect::route('activities.index');
    }

    public function capture(Request $request)
    {
        /** @var Activity $activity */
        $activity = Activity::findOrFail($request->get('capture_id'));
        if (!$request->get('data')) {
            return response()->json(['error' => 'no data', Response::HTTP_BAD_REQUEST]);
        }

        if ($activity->image) {
            return response()->json(['success' => 'activity has image already', 200]);
        }

        $activity->image = $request->get('data');
        $activity->save();
        return response()->json(['success' => 'success', 200]);
    }
}
