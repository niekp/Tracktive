<?php

namespace App\DataTransferModels;

use Spatie\LaravelData\Data;

final class ActivityData extends Data
{
    public function __construct(
        public \SplFileInfo $file,
    ) {}
}
