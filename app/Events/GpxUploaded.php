<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class GpxUploaded
{
    use Dispatchable;

    public function __construct(
        public $path
    )
    {
    }
}
