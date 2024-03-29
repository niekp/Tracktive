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
        public ?string $pace,
        public ?float $heart_rate = null,
        public ?float $altitude = null,
    ) {
    }
}
