<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\OfferReservationRequest;
use App\Http\Resources\API\OfferReservedResource;
use App\Services\ReserveOffer\ReserveOfferHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class OffersController extends Controller
{
    public function reservation(
        int $id,
        OfferReservationRequest $request,
        ReserveOfferHandler $handler
    ): JsonResponse {
        $reservation = $handler->handle(
            $request->getCommand($id)
        );

        return OfferReservedResource::make($reservation)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
