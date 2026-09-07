<?php

use App\Models\Offer;
use App\Models\Property;

function createPropertyWithOffer(
    array $property = [],
    array $offer = [],
): Property {
    $property = Property::factory()->create($property);

    Offer::factory()->create([
        'property_id' => $property->id,
        ...$offer,
    ]);

    return $property;
}

it('successfully searches the cheapest offer', function () {
    createPropertyWithOffer(
        ['city' => 'Barcelona'],
        [
            'price' => 50000,
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
        ],
    );

    createPropertyWithOffer(
        ['city' => 'Madrid'],
        [
            'price' => 70000,
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
        ],
    );

    $filters = [
        'check_in' => '2026-10-10',
        'check_out' => '2026-10-15',
        'guests' => 2,
    ];

    $response = $this->getJson(route('properties.index', $filters));

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                [
                    'code',
                    'name',
                    'city',
                    'best_offer' => [
                        'id',
                        'supplier',
                        'price',
                        'currency',
                        'available_units',
                        'expires_at',
                    ],
                ],
            ],
            'links' => [
                'next',
                'prev',
            ],
            'meta' => [
                'per_page',
            ],
        ]);

    expect($response->json('data.0.best_offer.price'))->toBe(50000);
    expect($response->json('data.1.best_offer.price'))->toBe(70000);
})->group('search');

it('selects the cheapest offer for each property', function () {
    $property = Property::factory()->create();

    Offer::factory()->create([
        'property_id' => $property->id,
        'price' => 70000,
        'check_in' => '2026-10-10',
        'check_out' => '2026-10-15',
    ]);

    $cheapestOffer = Offer::factory()->create([
        'property_id' => $property->id,
        'price' => 50000,
        'check_in' => '2026-10-10',
        'check_out' => '2026-10-15',
    ]);

    $response = $this->getJson(route('properties.index', [
        'check_in' => '2026-10-10',
        'check_out' => '2026-10-15',
        'guests' => 2,
    ]));

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.best_offer.id', $cheapestOffer->id)
        ->assertJsonPath('data.0.best_offer.price', 50000);
})->group('search');

it('filters properties by city', function () {
    createPropertyWithOffer(
        ['city' => 'Barcelona'],
        [
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
            'price' => 50000,
        ],
    );

    createPropertyWithOffer(
        ['city' => 'Madrid'],
        [
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
            'price' => 40000,
        ],
    );

    $response = $this->getJson(route('properties.index', [
        'city' => 'Barcelona',
        'check_in' => '2026-10-10',
        'check_out' => '2026-10-15',
        'guests' => 2,
    ]));

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.city', 'Barcelona');
})->group('search');

it('returns properties from all cities when city is not specified', function () {
    createPropertyWithOffer(
        ['city' => 'Barcelona'],
        [
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
        ],
    );

    createPropertyWithOffer(
        ['city' => 'Madrid'],
        [
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
        ],
    );

    $response = $this->getJson(route('properties.index', [
        'check_in' => '2026-10-10',
        'check_out' => '2026-10-15',
        'guests' => 2,
    ]));

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data');
})->group('search');

it('excludes offers that cannot accommodate guests', function () {
    createPropertyWithOffer(
        [],
        [
            'max_guests' => 1,
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
        ],
    );

    $response = $this->getJson(route('properties.index', [
        'check_in' => '2026-10-10',
        'check_out' => '2026-10-15',
        'guests' => 2,
    ]));

    $response
        ->assertOk()
        ->assertJsonCount(0, 'data');
})->group('search');

it('excludes offers with no available units', function () {
    createPropertyWithOffer(
        [],
        [
            'available_units' => 0,
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
        ],
    );

    $response = $this->getJson(route('properties.index', [
        'check_in' => '2026-10-10',
        'check_out' => '2026-10-15',
        'guests' => 2,
    ]));

    $response
        ->assertOk()
        ->assertJsonCount(0, 'data');
})->group('search');

it('excludes expired offers', function () {
    createPropertyWithOffer(
        [],
        [
            'expires_at' => now()->subMinute(),
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
        ],
    );

    $response = $this->getJson(route('properties.index', [
        'check_in' => '2026-10-10',
        'check_out' => '2026-10-15',
        'guests' => 2,
    ]));

    $response
        ->assertOk()
        ->assertJsonCount(0, 'data');
})->group('search');

it('returns empty result when there are no matching offers', function () {
    createPropertyWithOffer(
        [],
        [
            'check_in' => '2026-11-10',
            'check_out' => '2026-11-15',
        ],
    );

    $response = $this->getJson(route('properties.index', [
        'check_in' => '2026-10-10',
        'check_out' => '2026-10-15',
        'guests' => 2,
    ]));

    $response
        ->assertOk()
        ->assertJsonCount(0, 'data');
})->group('search');

it('requires check-in date', function () {
    $response = $this->getJson(route('properties.index', [
        'check_out' => '2026-10-15',
        'guests' => 2,
    ]));

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['check_in']);
})->group('search');

it('requires check-out date', function () {
    $response = $this->getJson(route('properties.index', [
        'check_in' => '2026-10-10',
        'guests' => 2,
    ]));

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['check_out']);
})->group('search');

it('requires guests', function () {
    $response = $this->getJson(route('properties.index', [
        'check_in' => '2026-10-10',
        'check_out' => '2026-10-15',
    ]));

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['guests']);
})->group('search');

it('rejects check-out date before check-in date', function () {
    $response = $this->getJson(route('properties.index', [
        'check_in' => '2026-10-15',
        'check_out' => '2026-10-10',
        'guests' => 2,
    ]));

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['check_out']);
})->group('search');

it('selects the offer with the lowest id when prices are equal', function () {
    $property = Property::factory()->create();

    $firstOffer = Offer::factory()->create([
        'property_id' => $property->id,
        'price' => 50000,
        'check_in' => '2026-10-10',
        'check_out' => '2026-10-15',
    ]);

    $secondOffer = Offer::factory()->create([
        'property_id' => $property->id,
        'price' => 50000,
        'check_in' => '2026-10-10',
        'check_out' => '2026-10-15',
    ]);

    $response = $this->getJson(route('properties.index', [
        'check_in' => '2026-10-10',
        'check_out' => '2026-10-15',
        'guests' => 2,
    ]));

    $response
        ->assertOk()
        ->assertJsonPath('data.0.best_offer.id', $firstOffer->id);
})->group('search');
