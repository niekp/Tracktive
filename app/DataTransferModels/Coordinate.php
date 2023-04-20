<?php

namespace App\DataTransferModels;

use DateTime;
use Spatie\LaravelData\Data;

final class Coordinate extends Data
{
    public function __construct(
        public float $latitude,
        public float $longitude,
        public DateTime $time,
    ) {
    }
}
