<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\PropertiesSearchRequest;
use App\Http\Resources\API\PropertySearchResource;
use App\Services\PropertiesSearch\PropertiesSearchService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertiesController extends Controller
{
    public function index(
        PropertiesSearchRequest $request,
        PropertiesSearchService $service
    ): AnonymousResourceCollection {
        $properties = $service->handle($request->validated());

        return PropertySearchResource::collection($properties);
    }
}
