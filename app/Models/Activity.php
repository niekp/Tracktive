<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property string $type
 * @property Carbon date
 */
class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'date'
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function gpxes(): HasMany
    {
        return $this->hasMany(Gpx::class);
    }

    public function gpx(): HasOne
    {
        return $this->hasOne(Gpx::class)->orderByDesc('version');
    }
}
