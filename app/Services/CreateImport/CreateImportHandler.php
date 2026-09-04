<?php

declare(strict_types=1);

namespace App\Services\CreateImport;

use App\Enums\ImportStatus;
use App\Jobs\ProcessImportJob;
use App\Models\Import;
use App\Models\Supplier;

class CreateImportHandler
{
    public function handle(CreateImportCommand $command): Import
    {
        $supplier = Supplier::query()
            ->where('name', $command->supplier)
            ->firstOrFail();

        $import = Import::firstOrCreate(
            [
                'supplier_id' => $supplier->id,
                'external_import_id' => $command->externalImportId,
            ],
            [
                'status' => ImportStatus::Pending,
                'payload' => $command->payload,
                'sent_at' => $command->sentAt,
            ],
        );

        if ($import->wasRecentlyCreated) {
            ProcessImportJob::dispatch($import->id);
        }

        return $import;
    }
}
