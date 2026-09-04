<?php

namespace Tests\Fixtures;

class ImportPayloadBuilder
{
    private array $payload;

    private function __construct()
    {
        $this->payload = [
            'supplier' => 'supplier-a',
            'external_import_id' => 'import-2026-09-01-001',
            'sent_at' => '2026-09-01T10:00:00Z',
            'offers' => [
                OfferPayloadBuilder::create()->build(),
            ],
        ];
    }

    public static function create(): self
    {
        return new self;
    }

    public function withoutSupplier(): self
    {
        unset($this->payload['supplier']);

        return $this;
    }

    public function withSupplier(string $supplier): self
    {
        $this->payload['supplier'] = $supplier;

        return $this;
    }

    public function withoutExternalImportId(): self
    {
        unset($this->payload['external_import_id']);

        return $this;
    }

    public function withExternalImportId(string $externalImportId): self
    {
        $this->payload['external_import_id'] = $externalImportId;

        return $this;
    }

    public function withoutSentAt(): self
    {
        unset($this->payload['sent_at']);

        return $this;
    }

    public function withSentAt(string $sentAt): self
    {
        $this->payload['sent_at'] = $sentAt;

        return $this;
    }

    public function withoutOffers(): self
    {
        unset($this->payload['offers']);

        return $this;
    }

    public function withInvalidOffers(): self
    {
        $this->payload['offers'] = 'wrong';

        return $this;
    }

    public function withOffers(array $offers): self
    {
        $this->payload['offers'] = $offers;

        return $this;
    }

    public function withOffer(OfferPayloadBuilder $builder): self
    {
        $this->payload['offers'] = [
            $builder->build(),
        ];

        return $this;
    }

    public function build(): array
    {
        return $this->payload;
    }
}
