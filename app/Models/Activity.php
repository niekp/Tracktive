<?php

namespace App\Models;

use App\DataTransferModels\ActivityResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property string $type
 * @property Carbon $date
 * @property ActivityResource $data
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
        'image',
    ];

    protected $casts = [
        'date' => 'datetime',
        'data' => ActivityResource::class,
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

    public function getData(): ?ActivityResource
    {
        if (!$this->data
            && $this->gpx
            && ($data = ActivityResource::fromGpx($this->gpx))
        ) {
            $this->data = $data;
            $this->save();
        }

        return $this->data ?? null;
    }
}
