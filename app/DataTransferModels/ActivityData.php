<?php

namespace App\DataTransferModels;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class ActivityData extends Data
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
}
