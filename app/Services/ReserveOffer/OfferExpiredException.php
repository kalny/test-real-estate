<?php

declare(strict_types=1);

namespace App\Services\ReserveOffer;

use RuntimeException;

class OfferExpiredException extends RuntimeException
{
    public static function withOfferId(int $offerId): self
    {
        return new self("Offer {$offerId} was expired.");
    }
}
