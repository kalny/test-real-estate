<?php

declare(strict_types=1);

namespace App\Services\PropertiesSearch;

use App\Models\Offer;
use App\Models\Property;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PropertiesSearchService
{
    public function handle(array $filters): LengthAwarePaginator
    {
        $rankedOffers = Offer::query()
            ->select([
                'offers.id',
                'offers.property_id',
                'offers.supplier_id',
                'offers.price',
                'offers.currency',
                'offers.available_units',
                'offers.expires_at',
                DB::raw('
                    ROW_NUMBER() OVER (
                        PARTITION BY offers.property_id
                        ORDER BY offers.price ASC, offers.id ASC
                    ) AS offer_rank
                '),
            ])
            ->whereDate('offers.check_in', $filters['check_in'])
            ->whereDate('offers.check_out', $filters['check_out'])
            ->where('offers.max_guests', '>=', $filters['guests'])
            ->where('offers.available_units', '>', 0)
            ->where('offers.expires_at', '>', CarbonImmutable::now());

        return Property::query()
            ->joinSub($rankedOffers, 'ranked_offers', function ($join) {
                $join->on(
                    'ranked_offers.property_id',
                    '=',
                    'properties.id'
                );
            })
            ->join(
                'suppliers',
                'suppliers.id',
                '=',
                'ranked_offers.supplier_id'
            )
            ->where('ranked_offers.offer_rank', 1)
            ->when(
                $filters['city'] ?? null,
                fn (Builder $query, string $city) => $query->where('properties.city', $city)
            )
            ->select([
                'properties.id',
                'properties.code',
                'properties.name',
                'properties.city',

                'ranked_offers.id as offer_id',
                'suppliers.name as supplier_name',
                'ranked_offers.price as offer_price',
                'ranked_offers.currency as offer_currency',
                'ranked_offers.available_units as offer_available_units',
                'ranked_offers.expires_at as offer_expires_at',
            ])
            ->paginate(20);
    }
}
