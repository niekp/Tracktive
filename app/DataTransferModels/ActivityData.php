<?php

namespace App\DataTransferModels;

use Spatie\DataTransferObject\DataTransferObject;

final class ActivityData extends DataTransferObject
{
    public \SplFileInfo $file;

}
