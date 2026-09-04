<?php

use App\Enums\ImportStatus;
use App\Jobs\ProcessImportJob;
use App\Models\Supplier;
use App\Services\CreateImport\CreateImportCommand;
use App\Services\CreateImport\CreateImportHandler;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Bus;
use Tests\Fixtures\ImportPayloadBuilder;

it('successfully created import', function () {
    Bus::fake();

    $supplier = Supplier::factory()->create([
        'name' => 'test-supplier',
    ]);

    $payload = ImportPayloadBuilder::create()
        ->withSupplier('test-supplier')
        ->withExternalImportId('import-2026-09-01-001')
        ->build();

    $command = new CreateImportCommand(
        supplier: 'test-supplier',
        externalImportId: 'import-2026-09-01-001',
        payload: $payload,
        sentAt: CarbonImmutable::now()
    );

    $handler = app(CreateImportHandler::class);

    $import = $handler->handle($command);

    $this->assertDatabaseHas('imports', [
        'supplier_id' => $supplier->id,
        'status' => ImportStatus::Pending,
        'external_import_id' => 'import-2026-09-01-001',
    ]);

    Bus::assertDispatched(ProcessImportJob::class, function (ProcessImportJob $job) use ($import) {
        return $job->importId === $import->id;
    });
});

it('throws an exception if the provider is invalid', function () {
    Bus::fake();

    $payload = ImportPayloadBuilder::create()
        ->withSupplier('test-supplier')
        ->withExternalImportId('import-2026-09-01-001')
        ->build();

    $command = new CreateImportCommand(
        supplier: 'test-supplier',
        externalImportId: 'import-2026-09-01-001',
        payload: $payload,
        sentAt: CarbonImmutable::now()
    );

    $handler = app(CreateImportHandler::class);

    $handler->handle($command);

    $this->assertDatabaseMissing('imports');

    Bus::assertNotDispatched(ProcessImportJob::class);
})->throws(ModelNotFoundException::class);

it('resubmitting import does not create a second import', function () {
    Bus::fake();

    Supplier::factory()->create([
        'name' => 'test-supplier',
    ]);

    $payload = ImportPayloadBuilder::create()
        ->withSupplier('test-supplier')
        ->withExternalImportId('import-2026-09-01-001')
        ->build();

    $command = new CreateImportCommand(
        supplier: 'test-supplier',
        externalImportId: 'import-2026-09-01-001',
        payload: $payload,
        sentAt: CarbonImmutable::now()
    );

    $handler = app(CreateImportHandler::class);

    $handler->handle($command);
    $handler->handle($command);

    $this->assertDatabaseCount('imports', 1);

    Bus::assertDispatchedOnce(ProcessImportJob::class);
});

it('resubmitting the import with a different supplier successfully creates a second import', function () {
    Bus::fake();

    Supplier::factory()->create([
        'name' => 'test-supplier',
    ]);

    Supplier::factory()->create([
        'name' => 'other-supplier',
    ]);

    $payload = ImportPayloadBuilder::create()
        ->withSupplier('test-supplier')
        ->withExternalImportId('import-2026-09-01-001')
        ->build();

    $command = new CreateImportCommand(
        supplier: 'test-supplier',
        externalImportId: 'import-2026-09-01-001',
        payload: $payload,
        sentAt: CarbonImmutable::now()
    );

    $commandWithOtherSupplier = new CreateImportCommand(
        supplier: 'other-supplier',
        externalImportId: 'import-2026-09-01-001',
        payload: $payload,
        sentAt: CarbonImmutable::now()
    );

    $handler = app(CreateImportHandler::class);

    $handler->handle($command);
    $handler->handle($commandWithOtherSupplier);

    $this->assertDatabaseCount('imports', 2);

    Bus::assertDispatchedTimes(ProcessImportJob::class, 2);
});
