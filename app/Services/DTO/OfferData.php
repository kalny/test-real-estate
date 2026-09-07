<?php

declare(strict_types=1);

namespace App\Services\DTO;

use App\Enums\Currency;
use Carbon\CarbonImmutable;

final readonly class OfferData
{
    public function __construct(
        public string $externalId,
        public PropertyData $property,
        public CarbonImmutable $checkIn,
        public CarbonImmutable $checkOut,
        public int $maxGuests,
        public int $price,
        public Currency $currency,
        public int $availableUnits,
        public CarbonImmutable $expiresAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            externalId: $data['external_id'],
            property: PropertyData::fromArray($data['property']),
            checkIn: CarbonImmutable::parse($data['check_in']),
            checkOut: CarbonImmutable::parse($data['check_out']),
            maxGuests: $data['max_guests'],
            price: $data['price'],
            currency: Currency::from($data['currency']),
            availableUnits: $data['available_units'],
            expiresAt: CarbonImmutable::parse($data['expires_at']),
        );
    }
}
