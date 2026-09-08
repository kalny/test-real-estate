<?php

declare(strict_types=1);

namespace Tests\Fixtures;

class PropertyPayloadBuilder
{
    private array $payload;

    private function __construct()
    {
        $this->payload = [
            'code' => 'BCN-0001',
            'name' => 'Apartment near Sagrada Familia',
            'city' => 'Barcelona',
        ];
    }

    public static function create(): self
    {
        return new self;
    }

    public function withoutCode(): self
    {
        unset($this->payload['code']);

        return $this;
    }

    public function withCode(string $code): self
    {
        $this->payload['code'] = $code;

        return $this;
    }

    public function withoutName(): self
    {
        unset($this->payload['name']);

        return $this;
    }

    public function withName(string $name): self
    {
        $this->payload['name'] = $name;

        return $this;
    }

    public function withoutCity(): self
    {
        unset($this->payload['city']);

        return $this;
    }

    public function build(): array
    {
        return $this->payload;
    }
}
