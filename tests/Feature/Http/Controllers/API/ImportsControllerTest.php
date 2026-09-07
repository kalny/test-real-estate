<?php

use App\Jobs\ProcessImportJob;
use App\Models\Offer;
use App\Models\Supplier;
use Illuminate\Support\Facades\Bus;
use Tests\Fixtures\ImportPayloadBuilder;
use Tests\Fixtures\OfferPayloadBuilder;
use Tests\Fixtures\PropertyPayloadBuilder;

it('successfully load imports', function () {
    Bus::fake();

    Supplier::factory()->create([
        'name' => 'test-supplier',
    ]);

    $payload = ImportPayloadBuilder::create()
        ->withSupplier('test-supplier')
        ->withExternalImportId('import-2026-09-01-001')
        ->build();

    $response = $this->postJson(route('imports.load'), $payload);

    $response
        ->assertAccepted()
        ->assertJsonStructure([
            'data' => [
                'id',
                'status',
            ],
        ]);

    Bus::assertDispatched(ProcessImportJob::class);
});

it('rejects invalid import payload', function (array $payload, array $errors) {
    Bus::fake();

    Supplier::factory()->create([
        'name' => 'test-supplier',
    ]);

    $this->postJson(route('imports.load'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($errors);

    Bus::assertNotDispatched(ProcessImportJob::class);
})->with([
    'missing supplier' => [
        ImportPayloadBuilder::create()
            ->withoutSupplier()
            ->build(),
        ['supplier'],
    ],
    'unknown supplier' => [
        ImportPayloadBuilder::create()
            ->withSupplier('wrong-supplier')
            ->build(),
        ['supplier'],
    ],
    'missing external_import_id' => [
        ImportPayloadBuilder::create()
            ->withoutExternalImportId()
            ->build(),
        ['external_import_id'],
    ],
    'missing sent_at' => [
        ImportPayloadBuilder::create()
            ->withoutSentAt()
            ->build(),
        ['sent_at'],
    ],
    'invalid sent_at format' => [
        ImportPayloadBuilder::create()
            ->withSentAt('wrong-date')
            ->build(),
        ['sent_at'],
    ],
    'missing offers' => [
        ImportPayloadBuilder::create()
            ->withoutOffers()
            ->build(),
        ['offers'],
    ],
    'invalid offers' => [
        ImportPayloadBuilder::create()
            ->withInvalidOffers()
            ->build(),
        ['offers'],
    ],
    'empty offers' => [
        ImportPayloadBuilder::create()
            ->withOffers([])
            ->build(),
        ['offers'],
    ],
    'missing offer external_id' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withoutExternalId())
            ->build(),
        ['offers.0.external_id'],
    ],
    'missing offer property' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withoutProperty())
            ->build(),
        ['offers.0.property'],
    ],
    'invalid offer property' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withInvalidProperty())
            ->build(),
        ['offers.0.property'],
    ],
    'missing offer property code' => [
        ImportPayloadBuilder::create()
            ->withOffer(
                OfferPayloadBuilder::create()
                    ->withProperty(
                        PropertyPayloadBuilder::create()->withoutCode()
                    )
            )
            ->build(),
        ['offers.0.property.code'],
    ],
    'missing offer property name' => [
        ImportPayloadBuilder::create()
            ->withOffer(
                OfferPayloadBuilder::create()
                    ->withProperty(
                        PropertyPayloadBuilder::create()->withoutName()
                    )
            )
            ->build(),
        ['offers.0.property.name'],
    ],
    'missing offer property city' => [
        ImportPayloadBuilder::create()
            ->withOffer(
                OfferPayloadBuilder::create()
                    ->withProperty(
                        PropertyPayloadBuilder::create()->withoutCity()
                    )
            )
            ->build(),
        ['offers.0.property.city'],
    ],
    'missing offer check_in' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withoutCheckIn())
            ->build(),
        ['offers.0.check_in'],
    ],
    'invalid offer check_in' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withInvalidCheckIn())
            ->build(),
        ['offers.0.check_in'],
    ],
    'missing offer check_out' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withoutCheckOut())
            ->build(),
        ['offers.0.check_out'],
    ],
    'invalid offer check_out' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withInvalidCheckOut())
            ->build(),
        ['offers.0.check_out'],
    ],
    'check_in after check_out' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withCheckInAfterCheckOut())
            ->build(),
        ['offers.0.check_out'],
    ],
    'missing offer max_guests' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withoutMaxGuests())
            ->build(),
        ['offers.0.max_guests'],
    ],
    'invalid offer max_guests' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withInvalidMaxGuests())
            ->build(),
        ['offers.0.max_guests'],
    ],
    'missing offer price' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withoutPrice())
            ->build(),
        ['offers.0.price'],
    ],
    'invalid offer price' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withInvalidPrice())
            ->build(),
        ['offers.0.price'],
    ],
    'missing offer currency' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withoutCurrency())
            ->build(),
        ['offers.0.currency'],
    ],
    'invalid offer currency' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withInvalidCurrency())
            ->build(),
        ['offers.0.currency'],
    ],
    'missing offer available_units' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withoutAvailableUnits())
            ->build(),
        ['offers.0.available_units'],
    ],
    'invalid offer available_units' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withInvalidAvailableUnits())
            ->build(),
        ['offers.0.available_units'],
    ],
    'missing offer expires_at' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withoutExpiresAt())
            ->build(),
        ['offers.0.expires_at'],
    ],
    'invalid offer expires_at' => [
        ImportPayloadBuilder::create()
            ->withOffer(OfferPayloadBuilder::create()->withInvalidExpiresAt())
            ->build(),
        ['offers.0.expires_at'],
    ],
]);

it('get import status', function () {
    $offer = Offer::factory()->create();

    $response = $this->getJson(route('imports.status', $offer->import_id));

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'id',
                'supplier',
                'external_import_id',
                'sent_at',
                'status',
                'total_offers',
                'processed_offers',
                'error',
                'created_at',
                'completed_at',
            ],
        ]);

    expect($response->json('data.processed_offers'))->toBe(1);
});
