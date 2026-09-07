<?php

declare(strict_types=1);

namespace App\Services\ReserveOffer;

final readonly class ReserveOfferCommand
{
    public function __construct(
        public int $offerId,
        public string $clientReference,
        public string $customerName,
        public string $customerEmail,
    ) {}
}
