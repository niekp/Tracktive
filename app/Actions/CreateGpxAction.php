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
        $path = Storage::path('/gpx');
        $filename = sprintf("%d_%d.gpx", $activity->id, $version);

        rename($gpx->getRealPath(), $path . '/' . $filename);

        return $activity->gpxes()->create([
            'file' => 'gpx/' . $filename,
            'version' => $version,
        ]);
    }
}
