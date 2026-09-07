<?php

declare(strict_types=1);

namespace App\Services\ReserveOffer;

use RuntimeException;

class InsufficientUnitsException extends RuntimeException
{
    public static function withOfferId(int $offerId): self
    {
        return new self("Insufficient Units in offer {$offerId}.");
    }
}
