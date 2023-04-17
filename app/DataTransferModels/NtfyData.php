<?php

namespace App\DataTransferModels;

use Spatie\LaravelData\Data;

final class NtfyData extends Data
{
    public string $id;
    public int $time;
    public ?int $expires;
    public string $event;
    public string $topic;
    public ?string $message;
    public ?NtfyAttachmentData $attachment;
    public ?array $tags;
}
