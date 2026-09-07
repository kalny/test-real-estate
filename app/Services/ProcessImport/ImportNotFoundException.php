<?php

declare(strict_types=1);

namespace App\Services\ProcessImport;

use RuntimeException;

class ImportNotFoundException extends RuntimeException
{
    public static function withImportId(int $importId): self
    {
        return new self("Import {$importId} not found.");
    }
}
