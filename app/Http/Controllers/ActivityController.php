<?php

namespace App\Http\Controllers;

use App\Actions\MergeGpxFilesAction;
use App\Events\GpxUploaded;
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
            ->whereNotNull('data')
            ->whereNotNull('type')
            ->with('persons')
            ->orderByDesc('date');

        if ($request->get('person')) {
            $activities->whereHas('persons', fn($q) => $q->whereIn('person_id', $request->get('person')));
        }
        if ($request->get('type')) {
            $activities->where('type', $request->get('type'));
        }
        if ($request->get('favorite') == 1) {
            $activities->where('favorite', true);
        }

        return view('activity.index', [
            'activities' => $activities->paginate(24),
            'types' => $types,
            'persons' => $persons,
            'selected_persons' => $request->get('person'),
            'selected_type' => $request->get('type'),
            'favorite' => $request->get('favorite') == 1,
        ]);
    }

    public function create()
    {
        return view('activity.create');
    }

    public function show(Activity $activity)
    {
        if (!$activity->type) {
            return Redirect::route('activities.edit', $activity->id);
        }

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

        if (is_array($gpx) && count($gpx) > 1) {
            $files = array_map(function (\SplFileInfo $file) {
                return $file->getRealPath();
            }, $gpx);

            $gpx = (new MergeGpxFilesAction)($files);
        } elseif (is_array($gpx) && count($gpx)) {
            $gpx = $gpx[0];
        }

        GpxUploaded::dispatch($gpx->getRealPath());
        $activity = Activity::query()->orderByDesc('id')->first();

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
        $activity->image = null;
        $activity->save();

        return Redirect::route('activities.show', $activity->id);
    }

    public function download(Activity $activity, ?int $version = null)
    {
        $gpx = $activity->gpxes()->orderBy('version')
            ->when($version, function ($query) use ($version) {
                return $query->where('version', $version);
            })
            ->first();
        if (!$gpx instanceof Gpx) {
            return Redirect::route('activities.show', $activity->id);
        }

        return response()->download(Storage::path($gpx->file));
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
        return response()->json(['success' => 'success']);
    }

    public function favorite(Activity $activity, Request $request)
    {
        $activity->favorite = $request->post('favorite') === true;
        $activity->save();

        return response()->json(['success' => 'success']);
    }
}
