<?php

namespace App\Http\Controllers;

use App\Actions\CreateActivityAction;
use App\DataTransferModels\ActivityData;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        return view('show');
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
}
