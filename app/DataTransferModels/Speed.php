<?php

namespace App\DataTransferModels;

use DateTime;
use Spatie\LaravelData\Data;

final class Speed extends Data
{
    public function __construct(
        public float $speed,
        public DateTime $time,
    ) {
    }
}
