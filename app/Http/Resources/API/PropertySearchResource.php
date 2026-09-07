<?php

declare(strict_types=1);

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertySearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var object {
         *     code: string,
         *     name: string,
         *     city: string,
         *     offer_id: int,
         *     supplier_name: string,
         *     offer_price: int,
         *     offer_currency: string,
         *     offer_available_units: int,
         *     offer_expires_at: CarbonImmutable
         * } $property
         */
        $property = $this->resource;

        return [
            'code' => $property->code,
            'name' => $property->name,
            'city' => $property->city,

            'best_offer' => [
                'id' => $property->offer_id,
                'supplier' => $property->supplier_name,
                'price' => $property->offer_price,
                'currency' => $property->offer_currency,
                'available_units' => $property->offer_available_units,
                'expires_at' => $property->offer_expires_at,
            ],
        ];
    }
}
