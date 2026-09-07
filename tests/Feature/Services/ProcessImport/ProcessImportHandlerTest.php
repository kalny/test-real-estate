<?php

use App\Enums\ImportStatus;
use App\Models\Import;
use App\Models\Offer;
use App\Models\Property;
use App\Models\Supplier;
use App\Services\ProcessImport\ImportNotFoundException;
use App\Services\ProcessImport\ProcessImportHandler;
use Carbon\CarbonImmutable;
use Tests\Fixtures\ImportPayloadBuilder;
use Tests\Fixtures\OfferPayloadBuilder;
use Tests\Fixtures\PropertyPayloadBuilder;

it('successfully processes a pending import with two offers', function () {
    $now = CarbonImmutable::now('UTC');
    $now = $now->setMicroseconds(0);
    CarbonImmutable::setTestNow($now);

    $supplier = Supplier::factory()->create([
        'name' => 'test-supplier',
    ]);

    $payload = ImportPayloadBuilder::create()
        ->withSupplier('test-supplier')
        ->withExternalImportId('import-2026-09-01-001')
        ->withOffers([
            OfferPayloadBuilder::create()
                ->withExternalId('offer-a-10001')
                ->withProperty(PropertyPayloadBuilder::create()->withCode('BCN-0001'))
                ->build(),
            OfferPayloadBuilder::create()
                ->withExternalId('offer-a-10002')
                ->withProperty(PropertyPayloadBuilder::create()->withCode('BCN-0002'))
                ->build(),
        ])
        ->build();

    $import = Import::factory()->create([
        'supplier_id' => $supplier->id,
        'external_import_id' => 'import-2026-09-01-001',
        'payload' => $payload,
    ]);

    $handler = app(ProcessImportHandler::class);

    $handler->handle($import->id);

    $this->assertDatabaseHas('imports', [
        'supplier_id' => $supplier->id,
        'external_import_id' => $import->external_import_id,
        'status' => ImportStatus::Completed,
        'completed_at' => CarbonImmutable::now(),
    ]);

    $this->assertDatabaseCount('offers', 2);

    $this->assertDatabaseCount('properties', 2);
});

it('does not process an already completed import', function () {
    $now = CarbonImmutable::now('UTC');
    $now = $now->setMicroseconds(0);
    CarbonImmutable::setTestNow($now);

    $supplier = Supplier::factory()->create([
        'name' => 'test-supplier',
    ]);

    $payload = ImportPayloadBuilder::create()
        ->withSupplier('test-supplier')
        ->withExternalImportId('import-2026-09-01-001')
        ->build();

    $import = Import::factory()->create([
        'supplier_id' => $supplier->id,
        'external_import_id' => 'import-2026-09-01-001',
        'status' => ImportStatus::Completed,
        'payload' => $payload,
        'completed_at' => CarbonImmutable::now()->subDay(),
    ]);

    $handler = app(ProcessImportHandler::class);

    $handler->handle($import->id);

    $import->refresh();

    expect($import->completed_at->eq(CarbonImmutable::now()->subDay()))->toBeTrue();
});

it('does not process an import in processing status', function () {
    $now = CarbonImmutable::now('UTC');
    $now = $now->setMicroseconds(0);
    CarbonImmutable::setTestNow($now);

    $supplier = Supplier::factory()->create([
        'name' => 'test-supplier',
    ]);

    $payload = ImportPayloadBuilder::create()
        ->withSupplier('test-supplier')
        ->withExternalImportId('import-2026-09-01-001')
        ->build();

    $import = Import::factory()->create([
        'supplier_id' => $supplier->id,
        'external_import_id' => 'import-2026-09-01-001',
        'status' => ImportStatus::Processing,
        'payload' => $payload,
    ]);

    $handler = app(ProcessImportHandler::class);

    $handler->handle($import->id);

    $import->refresh();

    expect($import->status)->toBe(ImportStatus::Processing);
});

it('does not process a failed import', function () {
    $now = CarbonImmutable::now('UTC');
    $now = $now->setMicroseconds(0);
    CarbonImmutable::setTestNow($now);

    $supplier = Supplier::factory()->create([
        'name' => 'test-supplier',
    ]);

    $payload = ImportPayloadBuilder::create()
        ->withSupplier('test-supplier')
        ->withExternalImportId('import-2026-09-01-001')
        ->build();

    $import = Import::factory()->create([
        'supplier_id' => $supplier->id,
        'external_import_id' => 'import-2026-09-01-001',
        'status' => ImportStatus::Failed,
        'payload' => $payload,
        'error' => 'Something went wrong',
    ]);

    $handler = app(ProcessImportHandler::class);

    $handler->handle($import->id);

    $import->refresh();

    expect($import->status)->toBe(ImportStatus::Failed);
});

it('throws an exception when import is not found', function () {
    $handler = app(ProcessImportHandler::class);

    $handler->handle(1);
})->throws(ImportNotFoundException::class);

it('reuses an existing property', function () {
    $now = CarbonImmutable::now('UTC');
    $now = $now->setMicroseconds(0);
    CarbonImmutable::setTestNow($now);

    $supplier = Supplier::factory()->create([
        'name' => 'test-supplier',
    ]);

    $property = Property::factory()->create([
        'code' => 'BCN-0001',
        'name' => 'Test property',
    ]);

    $payload = ImportPayloadBuilder::create()
        ->withSupplier('test-supplier')
        ->withExternalImportId('import-2026-09-01-001')
        ->withOffers([
            OfferPayloadBuilder::create()
                ->withExternalId('offer-a-10001')
                ->withProperty(
                    PropertyPayloadBuilder::create()
                        ->withCode('BCN-0001')
                )
                ->build(),
        ])
        ->build();

    $import = Import::factory()->create([
        'supplier_id' => $supplier->id,
        'external_import_id' => 'import-2026-09-01-001',
        'status' => ImportStatus::Pending,
        'payload' => $payload,
    ]);

    $handler = app(ProcessImportHandler::class);

    $handler->handle($import->id);

    $this->assertDatabaseHas('offers', [
        'import_id' => $import->id,
        'supplier_id' => $supplier->id,
        'property_id' => $property->id,
    ]);
});

it('creates a new property', function () {
    $now = CarbonImmutable::now('UTC');
    $now = $now->setMicroseconds(0);
    CarbonImmutable::setTestNow($now);

    $supplier = Supplier::factory()->create([
        'name' => 'test-supplier',
    ]);

    $payload = ImportPayloadBuilder::create()
        ->withSupplier('test-supplier')
        ->withExternalImportId('import-2026-09-01-001')
        ->withOffers([
            OfferPayloadBuilder::create()
                ->withExternalId('offer-a-10001')
                ->withProperty(
                    PropertyPayloadBuilder::create()
                        ->withCode('BCN-0001')
                        ->withName('Test property')
                )
                ->build(),
        ])
        ->build();

    $import = Import::factory()->create([
        'supplier_id' => $supplier->id,
        'external_import_id' => 'import-2026-09-01-001',
        'status' => ImportStatus::Pending,
        'payload' => $payload,
    ]);

    $handler = app(ProcessImportHandler::class);

    $handler->handle($import->id);

    $this->assertDatabaseHas('offers', [
        'import_id' => $import->id,
        'supplier_id' => $supplier->id,
    ]);

    $this->assertDatabaseHas('properties', [
        'code' => 'BCN-0001',
        'name' => 'Test property',
    ]);
});

it('updates an existing offer', function () {
    $now = CarbonImmutable::now('UTC');
    $now = $now->setMicroseconds(0);
    CarbonImmutable::setTestNow($now);

    $property = Property::factory()->create([
        'code' => 'BCN-0001',
        'name' => 'Test property',
    ]);

    $supplier = Supplier::factory()->create([
        'name' => 'test-supplier',
    ]);

    $offer = Offer::factory()->create([
        'supplier_id' => $supplier->id,
        'external_id' => 'offer-a-10001',
    ]);

    $payload = ImportPayloadBuilder::create()
        ->withSupplier('test-supplier')
        ->withExternalImportId('import-2026-09-01-001')
        ->withOffers([
            OfferPayloadBuilder::create()
                ->withExternalId('offer-a-10001')
                ->withProperty(
                    PropertyPayloadBuilder::create()
                        ->withCode('BCN-0001')
                        ->withName('Test property')
                )
                ->build(),
        ])
        ->build();

    $import = Import::factory()->create([
        'supplier_id' => $supplier->id,
        'external_import_id' => 'import-2026-09-01-001',
        'status' => ImportStatus::Pending,
        'payload' => $payload,
    ]);

    $handler = app(ProcessImportHandler::class);

    $handler->handle($import->id);

    $offer->refresh();

    expect($offer->import_id)->toBe($import->id);
    expect($offer->supplier_id)->toBe($supplier->id);
    expect($offer->property_id)->toBe($property->id);
});

it('creates an offer with the same external_id for another supplier', function () {
    $now = CarbonImmutable::now('UTC');
    $now = $now->setMicroseconds(0);
    CarbonImmutable::setTestNow($now);

    $property = Property::factory()->create([
        'code' => 'BCN-0001',
        'name' => 'Test property',
    ]);

    $supplier = Supplier::factory()->create([
        'name' => 'test-supplier',
    ]);

    $offer = Offer::factory()->create([
        'external_id' => 'offer-a-10001',
    ]);

    $payload = ImportPayloadBuilder::create()
        ->withSupplier('test-supplier')
        ->withExternalImportId('import-2026-09-01-001')
        ->withOffers([
            OfferPayloadBuilder::create()
                ->withExternalId('offer-a-10001')
                ->withProperty(
                    PropertyPayloadBuilder::create()
                        ->withCode('BCN-0001')
                        ->withName('Test property')
                )
                ->build(),
        ])
        ->build();

    $import = Import::factory()->create([
        'supplier_id' => $supplier->id,
        'external_import_id' => 'import-2026-09-01-001',
        'status' => ImportStatus::Pending,
        'payload' => $payload,
    ]);

    $handler = app(ProcessImportHandler::class);

    $handler->handle($import->id);

    $offer->refresh();

    expect($offer->import_id)->not()->toBe($import->id);
    expect($offer->supplier_id)->not()->toBe($supplier->id);
    expect($offer->property_id)->not()->toBe($property->id);
});

it('rolls back the import and marks it as failed when processing fails', function () {
    $supplier = Supplier::factory()->create([
        'name' => 'test-supplier',
    ]);

    $payload = ImportPayloadBuilder::create()
        ->withSupplier('test-supplier')
        ->withExternalImportId('import-2026-09-01-001')
        ->withOffers([
            OfferPayloadBuilder::create()
                ->withExternalId('offer-1')
                ->build(),
            OfferPayloadBuilder::create()
                ->withoutExternalId()
                ->build(),
        ])
        ->build();

    $import = Import::factory()->create([
        'supplier_id' => $supplier->id,
        'external_import_id' => 'import-2026-09-01-001',
        'status' => ImportStatus::Pending,
        'payload' => $payload,
    ]);

    $handler = app(ProcessImportHandler::class);

    expect(fn () => $handler->handle($import->id))
        ->toThrow(ErrorException::class);

    expect(Offer::query()->count())->toBe(0);
    expect(Property::query()->count())->toBe(0);

    $import->refresh();

    expect($import->status)->toBe(ImportStatus::Failed);
    expect($import->error)->toBe('Undefined array key "external_id"');
});
