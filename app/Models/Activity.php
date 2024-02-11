<?php

namespace App\Models;

use App\DataTransferModels\ActivityData;
use App\DataTransferModels\ActivitySummary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property string $type
 * @property Carbon $date
 * @property ActivityData $data
 * @property ActivitySummary $summary
 * @property string $image
 * @property Gpx $gpx
 */
class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'date',
        'data',
        'summary',
        'image',
    ];

    protected $casts = [
        'date' => 'datetime',
        'data' => ActivityData::class,
        'summary' => ActivitySummary::class,
    ];

    public function gpxes(): HasMany
    {
        return $this->hasMany(Gpx::class);
    }

    public function gpx(): HasOne
    {
        return $this->hasOne(Gpx::class)->orderByDesc('version');
    }

    public function persons(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'activity_people');
    }

    public function getData(): ?ActivityData
    {
        return $this->data;
    }

    public function getSummary(): ?ActivitySummary
    {
        return $this->summary ?? ActivitySummary::fromActivityData($this->getData());
    }
}
