<?php

use App\Models\Offer;

it('successfully creating offer reservation', function () {
    $offer = Offer::factory()->create();

    $response = $this->postJson(route('offers.reservation', $offer->id), [
        'client_reference' => 'web-order-9f782b1c',
        'customer_name' => 'John Smith',
        'customer_email' => 'john@example.com',
    ]);

    $response
        ->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
            ],
        ]);
})->group('reservation');

it('requires client_reference', function () {
    $offer = Offer::factory()->create();

    $response = $this->postJson(route('offers.reservation', $offer->id), [
        'customer_name' => 'John Smith',
        'customer_email' => 'john@example.com',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['client_reference']);
})->group('reservation');

it('requires customer_name', function () {
    $offer = Offer::factory()->create();

    $response = $this->postJson(route('offers.reservation', $offer->id), [
        'client_reference' => 'web-order-9f782b1c',
        'customer_email' => 'john@example.com',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['customer_name']);
})->group('reservation');

it('requires customer_email', function () {
    $offer = Offer::factory()->create();

    $response = $this->postJson(route('offers.reservation', $offer->id), [
        'client_reference' => 'web-order-9f782b1c',
        'customer_name' => 'John Smith',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['customer_email']);
})->group('reservation');
