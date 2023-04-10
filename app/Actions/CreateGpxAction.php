<?php

namespace App\Actions;

use App\Models\Activity;
use App\Models\Gpx;
use Illuminate\Support\Facades\Storage;

final class CreateGpxAction
{
    public function __invoke(Activity $activity, \SplFileInfo $gpx): Gpx
    {
        $version = $activity->gpxes()->count() + 1;
        $filename =  "$activity->id_$version.gpx";

        $gpx->move(Storage::path('/gpx'), $filename);

        return $activity->gpxes()->create([
            'file' => $filename,
            'version' => $version,
        ]);
    }
}
