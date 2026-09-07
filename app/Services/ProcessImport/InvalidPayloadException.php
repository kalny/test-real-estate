<?php

declare(strict_types=1);

namespace App\Services\ProcessImport;

use RuntimeException;

class InvalidPayloadException extends RuntimeException
{
    public static function create(int $importId, string $message): self
    {
        return new self("Invalid payload in import {$importId}: {$message}");
    }
}
