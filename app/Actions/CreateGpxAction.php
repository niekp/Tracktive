<?php

namespace App\Actions;

use App\Models\Activity;
use App\Models\Gpx;
use Illuminate\Support\Facades\Storage;

final class CreateGpxAction
{
    public function __invoke(Activity $activity, $path): Gpx
    {
        $version = $activity->gpxes()->count() + 1;
        $destination = Storage::path('/gpx');
        $filename = sprintf("%d_%d.gpx", $activity->id, $version);

        rename($path, $destination . '/' . $filename);

        $gpx = $activity->gpxes()->create([
            'file' => 'gpx/' . $filename,
            'version' => $version,
        ]);

        $activity->unsetRelation('gpx');

        return $gpx;
    }
}
