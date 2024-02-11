<?php

namespace App\DataTransferModels;

use Spatie\LaravelData\Data;

final class ActivitySummary extends Data
{
    public ?\DateTime $start = null;

    public ?\DateTime $stop = null;

    public float $distance = 0;

    public float $average_speed_active = 0;

    public float $average_speed_total = 0;

    public int $seconds_active = 0;

    public int $seconds_paused = 0;

    public static function fromActivityData(ActivityData $data): static
    {
        return static::from($data->toArray());
    }
}
