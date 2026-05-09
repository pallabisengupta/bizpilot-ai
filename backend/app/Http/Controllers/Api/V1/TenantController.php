<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenants\StoreTenantRequest;
use App\Http\Requests\Tenants\UpdateTenantRequest;
use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TenantController extends Controller
{
    public function __construct(
        private readonly TenantService $tenants,
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $tenantPage = $this->tenants->listForUser(
            $request->user(),
            (int) $request->integer('per_page', 15),
        );

        return TenantResource::collection($tenantPage);
    }

    public function store(StoreTenantRequest $request): JsonResponse
    {
        $tenant = $this->tenants->createForUser($request->user(), $request->validated());

        return response()->json([
            'message' => 'Tenant created successfully.',
            'data' => [
                'tenant' => new TenantResource($tenant),
            ],
        ], 201);
    }

    public function show(Tenant $tenant): JsonResponse
    {
        return response()->json([
            'data' => [
                'tenant' => new TenantResource($tenant),
            ],
        ]);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): JsonResponse
    {
        $tenant = $this->tenants->update($tenant, $request->validated());

        return response()->json([
            'message' => 'Tenant updated successfully.',
            'data' => [
                'tenant' => new TenantResource($tenant),
            ],
        ]);
    }

    public function destroy(Tenant $tenant): JsonResponse
    {
        $this->tenants->delete($tenant);

        return response()->json([
            'message' => 'Tenant deleted successfully.',
        ]);
    }
}
