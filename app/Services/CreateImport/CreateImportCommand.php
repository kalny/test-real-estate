<?php

declare(strict_types=1);

namespace App\Services\CreateImport;

use Carbon\CarbonImmutable;

final readonly class CreateImportCommand
{
    public function __construct(
        public string $supplier,
        public string $externalImportId,
        public array $payload,
        public CarbonImmutable $sentAt
    ) {}
}
