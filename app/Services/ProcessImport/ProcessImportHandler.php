<?php

declare(strict_types=1);

namespace App\Services\ProcessImport;

use App\Enums\ImportStatus;
use App\Models\Import;
use App\Models\Offer;
use App\Models\Property;
use App\Services\DTO\OfferData;
use App\Services\DTO\PropertyData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessImportHandler
{
    public function handle(int $importId): void
    {
        $import = DB::transaction(function () use ($importId): ?Import {
            $import = Import::query()
                ->lockForUpdate()
                ->find($importId);

            if (! $import) {
                throw ImportNotFoundException::withImportId($importId);
            }

            if ($import->status !== ImportStatus::Pending) {
                return null;
            }

            $import->update([
                'status' => ImportStatus::Processing,
            ]);

            return $import;
        });

        if (! $import) {
            return;
        }

        try {
            DB::transaction(function () use ($import) {
                $this->processImport($import);
            });
        } catch (Throwable $e) {
            $import->update([
                'status' => ImportStatus::Failed,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function processImport(Import $import): void
    {
        $offers = $import->payload['offers'];

        foreach ($offers as $offer) {
            $this->processOffer(
                $import,
                OfferData::fromArray($offer)
            );
        }

        $import->update([
            'status' => ImportStatus::Completed,
            'completed_at' => CarbonImmutable::now(),
        ]);
    }

    private function processOffer(Import $import, OfferData $offer): void
    {
        $property = $this->findOrCreateProperty($offer->property);

        Offer::updateOrCreate(
            [
                'supplier_id' => $import->supplier_id,
                'external_id' => $offer->externalId,
            ],
            [
                'import_id' => $import->id,
                'property_id' => $property->id,
                'check_in' => $offer->checkIn,
                'check_out' => $offer->checkOut,
                'max_guests' => $offer->maxGuests,
                'price' => $offer->price,
                'currency' => $offer->currency,
                'available_units' => $offer->availableUnits,
                'expires_at' => $offer->expiresAt,
            ],
        );
    }

    private function findOrCreateProperty(PropertyData $property): Property
    {
        return Property::firstOrCreate(
            [
                'code' => $property->code,
            ],
            [
                'name' => $property->name,
                'city' => $property->city,
            ],
        );
    }
}
