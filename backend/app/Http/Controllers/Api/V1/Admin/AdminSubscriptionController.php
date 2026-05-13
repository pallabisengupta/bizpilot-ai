<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminSubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return SubscriptionResource::collection(
            $this->subscriptions->list($request->only(['status', 'billing_status']), (int) $request->integer('per_page', 25)),
        );
    }

    public function markPaid(Subscription $subscription): JsonResponse
    {
        $subscription = $this->subscriptions->markStatus($subscription, 'active', 'paid');

        return response()->json([
            'message' => 'Subscription marked as paid.',
            'data' => ['subscription' => new SubscriptionResource($subscription)],
        ]);
    }

    public function cancel(Subscription $subscription): JsonResponse
    {
        $subscription = $this->subscriptions->markStatus($subscription, 'cancelled', 'cancelled');

        return response()->json([
            'message' => 'Subscription cancelled.',
            'data' => ['subscription' => new SubscriptionResource($subscription)],
        ]);
    }
}
