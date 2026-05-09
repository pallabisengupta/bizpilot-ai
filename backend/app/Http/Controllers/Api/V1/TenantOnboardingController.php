<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenants\OnboardingStepRequest;
use App\Http\Requests\Tenants\OnboardingTenantRequest;
use App\Http\Resources\TenantResource;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantOnboardingController extends Controller
{
    public function __construct(
        private readonly TenantService $tenants,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'tenant' => $request->user()->tenant
                    ? new TenantResource($request->user()->tenant)
                    : null,
                'steps' => [
                    'company_profile',
                    'business_contact',
                    'plan_selection',
                    'complete',
                ],
            ],
        ]);
    }

    public function store(OnboardingTenantRequest $request): JsonResponse
    {
        $tenant = $this->tenants->completeOnboarding($request->user(), $request->validated());

        return response()->json([
            'message' => 'Tenant onboarding completed successfully.',
            'data' => [
                'tenant' => new TenantResource($tenant),
            ],
        ]);
    }

    public function step(OnboardingStepRequest $request): JsonResponse
    {
        $tenant = $this->tenants->saveOnboardingStep($request->user(), $request->validated());

        return response()->json([
            'message' => 'Tenant onboarding step saved successfully.',
            'data' => [
                'tenant' => new TenantResource($tenant),
            ],
        ]);
    }
}
