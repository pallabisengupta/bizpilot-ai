<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminTenantController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return TenantResource::collection(
            Tenant::query()
                ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
                ->latest()
                ->paginate((int) $request->integer('per_page', 25)),
        );
    }

    public function activate(Tenant $tenant): JsonResponse
    {
        $tenant->forceFill(['status' => 'active'])->save();

        return response()->json([
            'message' => 'Tenant activated successfully.',
            'data' => ['tenant' => new TenantResource($tenant->refresh())],
        ]);
    }

    public function suspend(Tenant $tenant): JsonResponse
    {
        $tenant->forceFill(['status' => 'suspended'])->save();

        return response()->json([
            'message' => 'Tenant suspended successfully.',
            'data' => ['tenant' => new TenantResource($tenant->refresh())],
        ]);
    }
}
