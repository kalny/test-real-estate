<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreateImportRequest;
use App\Http\Resources\API\ImportCreatedResource;
use App\Http\Resources\API\ImportStatusResource;
use App\Models\Import;
use App\Services\CreateImport\CreateImportHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ImportsController extends Controller
{
    public function load(
        CreateImportRequest $request,
        CreateImportHandler $handler
    ): JsonResponse {
        $import = $handler->handle($request->getCommand());

        return ImportCreatedResource::make($import)
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function getStatus(int $id): JsonResponse
    {
        $import = Import::query()
            ->with('supplier')
            ->findOrFail($id);

        return ImportStatusResource::make($import)
            ->response();
    }
}
