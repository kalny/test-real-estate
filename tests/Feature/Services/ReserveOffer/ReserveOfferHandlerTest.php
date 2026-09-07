<?php

use App\Models\Offer;
use App\Models\Reservation;
use App\Services\ReserveOffer\InsufficientUnitsException;
use App\Services\ReserveOffer\OfferExpiredException;
use App\Services\ReserveOffer\ReserveOfferCommand;
use App\Services\ReserveOffer\ReserveOfferHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('successfully reserve offer', function () {
    $offer = Offer::factory()->create([
        'available_units' => 1,
    ]);

    $command = new ReserveOfferCommand(
        offerId: $offer->id,
        clientReference: 'web-order-9f782b1c',
        customerName: 'John Smith',
        customerEmail: 'john@example.com',
    );

    $handler = app(ReserveOfferHandler::class);

    $reservation = $handler->handle($command);

    expect($reservation->offer_id)->toBe($offer->id);
    expect($reservation->client_reference)->toBe('web-order-9f782b1c');
    expect($reservation->customer_name)->toBe('John Smith');
    expect($reservation->customer_email)->toBe('john@example.com');

    $this->assertDatabaseHas('reservations', [
        'offer_id' => $offer->id,
        'client_reference' => 'web-order-9f782b1c',
        'customer_name' => 'John Smith',
        'customer_email' => 'john@example.com',
    ]);

    expect($offer->fresh()->available_units)->toBe(0);
})->group('reservation');

it('throws exception when offer does not have available units', function () {
    $offer = Offer::factory()->create([
        'available_units' => 0,
    ]);

    $command = new ReserveOfferCommand(
        offerId: $offer->id,
        clientReference: 'web-order-9f782b1c',
        customerName: 'John Smith',
        customerEmail: 'john@example.com',
    );

    $this->expectException(InsufficientUnitsException::class);

    app(ReserveOfferHandler::class)->handle($command);
})->group('reservation');

it('throws exception when offer has expired', function () {
    $offer = Offer::factory()->create([
        'available_units' => 1,
        'expires_at' => now()->subMinute(),
    ]);

    $command = new ReserveOfferCommand(
        offerId: $offer->id,
        clientReference: 'web-order-9f782b1c',
        customerName: 'John Smith',
        customerEmail: 'john@example.com',
    );

    $this->expectException(OfferExpiredException::class);

    app(ReserveOfferHandler::class)->handle($command);
})->group('reservation');

it('returns existing reservation for duplicate client reference', function () {
    $offer = Offer::factory()->create([
        'available_units' => 2,
    ]);

    $reservation = Reservation::factory()->create([
        'offer_id' => $offer->id,
        'client_reference' => 'web-order-9f782b1c',
        'customer_name' => 'John Smith',
        'customer_email' => 'john@example.com',
    ]);

    $command = new ReserveOfferCommand(
        offerId: $offer->id,
        clientReference: 'web-order-9f782b1c',
        customerName: 'Different Name',
        customerEmail: 'different@example.com',
    );

    $result = app(ReserveOfferHandler::class)->handle($command);

    expect($result->id)->toBe($reservation->id);
    expect($result->customer_name)->toBe('John Smith');
    expect($result->customer_email)->toBe('john@example.com');

    expect($offer->fresh()->available_units)->toBe(2);

    expect(Reservation::query()
        ->where('offer_id', $offer->id)
        ->where('client_reference', 'web-order-9f782b1c')
        ->count()
    )->toBe(1);
})->group('reservation');

it('throws exception when offer does not exist', function () {
    $command = new ReserveOfferCommand(
        offerId: 999999,
        clientReference: 'web-order-9f782b1c',
        customerName: 'John Smith',
        customerEmail: 'john@example.com',
    );

    $this->expectException(ModelNotFoundException::class);

    app(ReserveOfferHandler::class)->handle($command);
})->group('reservation');
