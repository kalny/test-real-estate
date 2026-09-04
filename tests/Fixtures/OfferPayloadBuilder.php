<?php

namespace Tests\Fixtures;

class OfferPayloadBuilder
{
    private array $payload;

    private function __construct()
    {
        $this->payload = [
            'external_id' => 'offer-a-10001',
            'property' => PropertyPayloadBuilder::create()->build(),
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
            'max_guests' => 4,
            'price' => 72500,
            'currency' => 'EUR',
            'available_units' => 2,
            'expires_at' => '2026-09-10T23:59:59Z',
        ];
    }

    public static function create(): self
    {
        return new self;
    }

    public function withoutExternalId(): self
    {
        unset($this->payload['external_id']);

        return $this;
    }

    public function withoutProperty(): self
    {
        unset($this->payload['property']);

        return $this;
    }

    public function withInvalidProperty(): self
    {
        $this->payload['property'] = 'wrong';

        return $this;
    }

    public function withProperty(PropertyPayloadBuilder $builder): self
    {
        $this->payload['property'] = $builder->build();

        return $this;
    }

    public function withoutCheckIn(): self
    {
        unset($this->payload['check_in']);

        return $this;
    }

    public function withInvalidCheckIn(): self
    {
        $this->payload['check_in'] = 'wrong';

        return $this;
    }

    public function withoutCheckOut(): self
    {
        unset($this->payload['check_out']);

        return $this;
    }

    public function withInvalidCheckOut(): self
    {
        $this->payload['check_out'] = 'wrong';

        return $this;
    }

    public function withCheckInAfterCheckOut(): self
    {
        $this->payload['check_in'] = '2026-10-10';
        $this->payload['check_out'] = '2026-10-05';

        return $this;
    }

    public function withoutMaxGuests(): self
    {
        unset($this->payload['max_guests']);

        return $this;
    }

    public function withInvalidMaxGuests(): self
    {
        $this->payload['max_guests'] = 0;

        return $this;
    }

    public function withoutPrice(): self
    {
        unset($this->payload['price']);

        return $this;
    }

    public function withInvalidPrice(): self
    {
        $this->payload['price'] = -1;

        return $this;
    }

    public function withoutCurrency(): self
    {
        unset($this->payload['currency']);

        return $this;
    }

    public function withInvalidCurrency(): self
    {
        $this->payload['currency'] = 'wrong';

        return $this;
    }

    public function withoutAvailableUnits(): self
    {
        unset($this->payload['available_units']);

        return $this;
    }

    public function withInvalidAvailableUnits(): self
    {
        $this->payload['available_units'] = -1;

        return $this;
    }

    public function withoutExpiresAt(): self
    {
        unset($this->payload['expires_at']);

        return $this;
    }

    public function withInvalidExpiresAt(): self
    {
        $this->payload['expires_at'] = 'wrong';

        return $this;
    }

    public function build(): array
    {
        return $this->payload;
    }
}
