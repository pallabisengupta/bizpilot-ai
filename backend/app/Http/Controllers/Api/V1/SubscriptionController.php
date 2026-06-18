<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscriptions\SubscribeRequest;
use App\Http\Requests\Subscriptions\VerifyRazorpayPaymentRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\RazorpayCheckoutService;
use App\Services\SubscriptionService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
        private readonly RazorpayCheckoutService $checkout,
        private readonly TenantContext $tenantContext,
    ) {
    }

    public function current(Request $request): JsonResponse
    {
        $subscription = $this->subscriptions->current($this->tenant($request));

        return response()->json([
            'data' => [
                'subscription' => $subscription ? new SubscriptionResource($subscription) : null,
            ],
        ]);
    }

    public function subscribe(SubscribeRequest $request): JsonResponse
    {
        $subscription = $this->subscriptions->subscribe(
            $this->tenant($request),
            Plan::query()->findOrFail($request->validated('plan_id')),
        );

        return response()->json([
            'message' => 'Subscription activated successfully.',
            'data' => [
                'subscription' => new SubscriptionResource($subscription),
            ],
        ], 201);
    }

    public function checkout(SubscribeRequest $request): JsonResponse
    {
        $order = $this->checkout->createOrder(
            $this->tenant($request),
            Plan::query()->findOrFail($request->validated('plan_id')),
        );

        return response()->json([
            'data' => [
                'checkout' => $order,
            ],
        ]);
    }

    public function verify(VerifyRazorpayPaymentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $this->checkout->verifySignature(
            $data['razorpay_order_id'],
            $data['razorpay_payment_id'],
            $data['razorpay_signature'],
        );

        $subscription = $this->subscriptions->subscribe(
            $this->tenant($request),
            Plan::query()->findOrFail($data['plan_id']),
            [
                'provider' => 'razorpay',
                'razorpay_order_id' => $data['razorpay_order_id'],
                'razorpay_payment_id' => $data['razorpay_payment_id'],
            ],
        );

        return response()->json([
            'message' => 'Payment verified and subscription activated.',
            'data' => [
                'subscription' => new SubscriptionResource($subscription),
            ],
        ]);
    }

    private function tenant(Request $request): Tenant
    {
        $tenant = $this->tenantContext->get() ?? $request->user()?->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context is required.');
        }

        return $tenant;
    }
}
