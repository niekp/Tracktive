<?php

namespace App\DataTransferModels;

use Spatie\LaravelData\Data;

final class NtfyAttachmentData extends Data
{
    public string $name;
    public string $type;
    public int $size;
    public int $expires;
    public string $url;
}
