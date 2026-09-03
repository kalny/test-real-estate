<?php

namespace Database\Factories;

use App\Enums\ImportStatus;
use App\Models\Import;
use App\Models\Supplier;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Import>
 */
class ImportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'external_import_id' => 'import-'.fake()->unique()->randomNumber(),
            'payload' => [],
            'sent_at' => CarbonImmutable::now(),
        ];
    }

    public function withPayload(array $payload): static
    {
        return $this->state(fn (array $attributes) => [
            'payload' => $payload,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => CarbonImmutable::now(),
            'status' => ImportStatus::Completed,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ImportStatus::Processing,
        ]);
    }

    public function failed(string $error = 'Something went wrong'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ImportStatus::Failed,
            'error' => $error,
        ]);
    }
}
