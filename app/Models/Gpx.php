<?php

namespace App\Models;

use App\DataTransferModels\ActivityResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use phpGPX\phpGPX;

/**
 * @property string $file
 * @property int $version
 */
class Gpx extends Model
{
    use HasFactory;

    protected $fillable = [
        'file',
        'version'
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
