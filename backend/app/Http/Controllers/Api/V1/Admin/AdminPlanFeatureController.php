<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlanFeatureRequest;
use App\Http\Resources\PlanFeatureResource;
use App\Models\PlanFeature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminPlanFeatureController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PlanFeatureResource::collection(
            PlanFeature::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(100)
        );
    }

    public function store(PlanFeatureRequest $request): JsonResponse
    {
        $feature = PlanFeature::query()->create($request->validated());

        return response()->json([
            'message' => 'Plan feature created successfully.',
            'data' => ['feature' => new PlanFeatureResource($feature)],
        ], 201);
    }

    public function update(PlanFeatureRequest $request, PlanFeature $planFeature): JsonResponse
    {
        $planFeature->update($request->validated());

        return response()->json([
            'message' => 'Plan feature updated successfully.',
            'data' => ['feature' => new PlanFeatureResource($planFeature->refresh())],
        ]);
    }

    public function destroy(PlanFeature $planFeature): JsonResponse
    {
        $planFeature->delete();

        return response()->json(['message' => 'Plan feature deleted successfully.']);
    }
}
