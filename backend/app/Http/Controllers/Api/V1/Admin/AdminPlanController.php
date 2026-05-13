<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminPlanController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PlanResource::collection(Plan::query()->orderBy('sort_order')->paginate(25));
    }

    public function store(PlanRequest $request): JsonResponse
    {
        $plan = Plan::query()->create($request->validated());

        return response()->json([
            'message' => 'Plan created successfully.',
            'data' => ['plan' => new PlanResource($plan)],
        ], 201);
    }

    public function update(PlanRequest $request, Plan $plan): JsonResponse
    {
        $plan->update($request->validated());

        return response()->json([
            'message' => 'Plan updated successfully.',
            'data' => ['plan' => new PlanResource($plan->refresh())],
        ]);
    }

    public function destroy(Plan $plan): JsonResponse
    {
        $plan->delete();

        return response()->json(['message' => 'Plan deleted successfully.']);
    }
}
