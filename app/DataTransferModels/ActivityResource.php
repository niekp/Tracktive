<?php

namespace App\DataTransferModels;

use App\Cast\ActivityResourceCast;
use App\Models\Gpx;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use phpGPX\Models\GpxFile;
use phpGPX\phpGPX;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class ActivityResource extends Data
{
    public ?Carbon $start = null;

    public ?Carbon $stop = null;

    public float $distance = 0;

    public float $average_speed_active = 0;

    public float $average_speed_total = 0;

    public int $seconds_active = 0;

    public int $seconds_paused = 0;

    #[DataCollectionOf(Coordinate::class)]
    public ?DataCollection $coordinates = null;

    public static function fromGpx(Gpx $gpx): self
    {
        $file = phpGPX::load(Storage::path($gpx->file));
        return self::getStats($file);
    }

    private static function getStats(GpxFile $gpx_file): self
    {
        $data = new self();
        $previous_time = null;
        $coordinates = [];

        foreach ($gpx_file->tracks as $track) {
            foreach ($track->segments as $segment) {
                foreach ($segment->getPoints() as $point) {
                    $time = Carbon::createFromInterface($point->time);
                    if (!isset($data->start)) {
                        $data->start = $time;
                    }

                    $data->stop = $time;

                    $coordinates[] = new Coordinate(
                        $point->latitude,
                        $point->longitude,
                        $time,
                    );

                    $data->distance += $point->difference ?? 0.0;

                    $duration = 0;
                    if ($previous_time instanceof Carbon) {
                        $duration = $time->diffInSeconds($previous_time);
                    }

                    if ($duration && $point->difference / $duration > 0.5) { // 1.8km/u
                        $data->seconds_active += $duration;
                    } else {
                        $data->seconds_paused += $duration;
                    }

                    $previous_time = $time;
                }
            }
        }

        $data->coordinates = new DataCollection(Coordinate::class, $coordinates);
        $data->average_speed_active = round($data->distance / $data->seconds_active * 3.6, 2);
        $data->average_speed_total = round($data->distance / ($data->seconds_active + $data->seconds_paused) * 3.6, 2);
        $data->distance = round($data->distance / 1000, 2);

        return $data;
    }
}
