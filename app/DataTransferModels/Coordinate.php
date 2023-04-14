<?php

namespace App\DataTransferModels;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

final class Coordinate extends Data
{
    public function __construct(
        public float $latitude,
        public float $longitude,
        public Carbon $time,
    ) {
    }
}
