<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Models\Import;
use App\Models\Offer;
use App\Models\Property;
use App\Models\Supplier;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = CarbonImmutable::today()
            ->addDays(fake()->numberBetween(1, 30));

        $checkOut = $checkIn
            ->addDays(fake()->numberBetween(1, 14));

        $expiresAt = CarbonImmutable::now()
            ->addDays(fake()->numberBetween(1, 30));

        return [
            'import_id' => Import::factory(),
            'supplier_id' => Supplier::factory(),
            'property_id' => Property::factory(),
            'external_id' => 'offer-a-'.fake()->unique()->randomNumber(),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'max_guests' => 4,
            'price' => 72500,
            'currency' => Currency::EUR,
            'available_units' => 2,
            'expires_at' => $expiresAt,
        ];
    }
}
