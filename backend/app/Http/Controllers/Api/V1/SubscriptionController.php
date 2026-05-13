<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscriptions\SubscribeRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Plan;
use App\Services\SubscriptionService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
        private readonly TenantContext $tenantContext,
    ) {
    }

    public function current(): JsonResponse
    {
        $subscription = $this->subscriptions->current($this->tenantContext->get());

        return response()->json([
            'data' => [
                'subscription' => $subscription ? new SubscriptionResource($subscription) : null,
            ],
        ]);
    }

    public function subscribe(SubscribeRequest $request): JsonResponse
    {
        $subscription = $this->subscriptions->subscribe(
            $this->tenantContext->get(),
            Plan::query()->findOrFail($request->validated('plan_id')),
        );

        return response()->json([
            'message' => 'Subscription activated successfully.',
            'data' => [
                'subscription' => new SubscriptionResource($subscription),
            ],
        ], 201);
    }
}
