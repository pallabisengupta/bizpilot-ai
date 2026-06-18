<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RazorpayCheckoutService
{
    public function createOrder(Tenant $tenant, Plan $plan): array
    {
        $keyId = config('billing.razorpay.key_id');
        $keySecret = config('billing.razorpay.key_secret');

        if (! $keyId || ! $keySecret) {
            throw ValidationException::withMessages([
                'billing' => ['Razorpay keys are not configured.'],
            ]);
        }

        $currency = $plan->currency ?: config('billing.razorpay.currency', 'INR');
        $receipt = Str::limit("tenant_{$tenant->id}_plan_{$plan->id}_".Str::uuid(), 40, '');

        $response = Http::withBasicAuth($keyId, $keySecret)
            ->acceptJson()
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => $plan->price_amount,
                'currency' => $currency,
                'receipt' => $receipt,
                'notes' => [
                    'tenant_id' => (string) $tenant->id,
                    'plan_id' => (string) $plan->id,
                    'plan_slug' => $plan->slug,
                ],
            ]);

        if ($response->failed()) {
            throw ValidationException::withMessages([
                'billing' => [$response->json('error.description') ?: 'Unable to create Razorpay order.'],
            ]);
        }

        $order = $response->json();

        return [
            'key' => $keyId,
            'order_id' => $order['id'],
            'amount' => $order['amount'],
            'currency' => $order['currency'],
            'name' => 'BizPilot AI',
            'description' => $plan->name,
            'prefill' => [
                'email' => $tenant->email,
                'contact' => $tenant->phone,
            ],
            'notes' => $order['notes'] ?? [],
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
            ],
        ];
    }

    public function verifySignature(string $orderId, string $paymentId, string $signature): void
    {
        $secret = config('billing.razorpay.key_secret');

        if (! $secret) {
            throw ValidationException::withMessages([
                'billing' => ['Razorpay secret is not configured.'],
            ]);
        }

        $generatedSignature = hash_hmac('sha256', $orderId.'|'.$paymentId, $secret);

        if (! hash_equals($generatedSignature, $signature)) {
            throw ValidationException::withMessages([
                'razorpay_signature' => ['Invalid Razorpay payment signature.'],
            ]);
        }
    }
}
