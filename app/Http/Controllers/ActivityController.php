<?php

namespace App\Http\Controllers;

use App\Actions\CreateActivityAction;
use App\DataTransferModels\ActivityData;
use App\DataTransferModels\ActivityResource;
use App\Models\Activity;
use App\Models\Gpx;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

final class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $types = Activity::query()->whereNotNull('type')->select('type')->distinct()->pluck('type');
        $persons = Person::query()->orderBy('name')->get();

        $activities = Activity::query()
            ->with('persons')
            ->orderByDesc('date');

        if ($request->get('person')) {
            $activities->whereHas('persons', fn($q) => $q->whereIn('person_id', $request->get('person')));
        }
        if ($request->get('type')) {
            $activities->where('type', $request->get('type'));
        }

        return view('activity.index', [
            'activities' => $activities->get(),
            'types' => $types,
            'persons' => $persons,
            'selected_persons' => $request->get('person'),
            'selected_type' => $request->get('type'),
        ]);
    }

    public function create()
    {
        return view('activity.create');
    }

    public function show(Activity $activity)
    {
        return view('activity.show', [
            'activity' => $activity,
            'stats' => $activity->getData(),
        ]);
    }

    public function edit(Activity $activity)
    {
        $types = Activity::query()->whereNotNull('type')->select('type')->distinct()->pluck('type');
        $persons = Person::query()->orderBy('name')->get();

        return view('activity.edit', [
            'activity' => $activity,
            'types' => $types,
            'persons' => $persons,
        ]);
    }

    public function store(Request $request)
    {
        if (!$gpx = $request->file('gpx')) {
            return Redirect::route('create');
        }

        $activity = (new CreateActivityAction)(new ActivityData($gpx));

        return Redirect::route('activities.edit', $activity->id);
    }

    public function update(Activity $activity, Request $request)
    {
        $activity->type = $request->post('type');
        $persons = $request->post('person') ? Person::query()->whereIn('id', $request->post('person'))->get() : null;
        if ($persons) {
            $activity->persons()->sync($persons);
        } else {
            $activity->persons()->detach();
        }
        $activity->data = null;
        $activity->image = null;
        $activity->save();

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
