<?php

declare(strict_types=1);

namespace App\Services\ReserveOffer;

use App\Models\Offer;
use App\Models\Reservation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ReserveOfferHandler
{
    public function handle(ReserveOfferCommand $command): Reservation
    {
        return DB::transaction(function () use ($command): Reservation {
            $offer = Offer::query()
                ->lockForUpdate()
                ->findOrFail($command->offerId);

            $reservation = Reservation::query()->firstOrCreate(
                [
                    'offer_id' => $offer->id,
                    'client_reference' => $command->clientReference,
                ],
                [
                    'customer_name' => $command->customerName,
                    'customer_email' => $command->customerEmail,
                ],
            );

            if (! $reservation->wasRecentlyCreated) {
                return $reservation;
            }

            if ($offer->available_units < 1) {
                throw InsufficientUnitsException::withOfferId($offer->id);
            }

            if ($offer->expires_at <= CarbonImmutable::now()) {
                throw OfferExpiredException::withOfferId($offer->id);
            }

            $offer->decrement('available_units');

            return $reservation;
        });
    }
}
