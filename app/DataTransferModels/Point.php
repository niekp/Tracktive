<?php

namespace App\DataTransferModels;

use DateTime;
use Spatie\LaravelData\Data;

final class Point extends Data
{
    public function __construct(
        public float $latitude,
        public float $longitude,
        public DateTime $time,
        public bool $active,
        public float $speed,
        public ?float $heart_rate = null,
    ) {
    }
}
