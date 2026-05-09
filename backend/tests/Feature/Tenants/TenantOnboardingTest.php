<?php

namespace Tests\Feature\Tenants;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_onboarding_state_without_tenant(): void
    {
        $user = User::factory()->create([
            'tenant_id' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/tenants/onboarding');

        $response->assertOk()
            ->assertJsonPath('data.tenant', null)
            ->assertJsonPath('data.steps.0', 'company_profile');
    }

    public function test_user_can_save_onboarding_step(): void
    {
        $user = User::factory()->create([
            'tenant_id' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/tenants/onboarding/step', [
                'step' => 'company_profile',
                'company_name' => 'Pilot Works',
                'timezone' => 'UTC',
                'language' => 'en',
                'payload' => [
                    'industry' => 'services',
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Tenant onboarding step saved successfully.')
            ->assertJsonPath('data.tenant.status', 'onboarding')
            ->assertJsonPath('data.tenant.onboarding.current_step', 'company_profile');

        $this->assertNotNull($user->fresh()->tenant_id);
    }

    public function test_user_can_complete_onboarding(): void
    {
        $user = User::factory()->create([
            'tenant_id' => null,
        ]);
        $plan = Plan::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/tenants/onboarding', [
                'company_name' => 'Pilot Works',
                'industry' => 'services',
                'phone' => '+15551234567',
                'email' => 'hello@pilot.test',
                'website' => 'https://pilot.test',
                'timezone' => 'UTC',
                'language' => 'en',
                'plan_id' => $plan->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Tenant onboarding completed successfully.')
            ->assertJsonPath('data.tenant.status', 'active')
            ->assertJsonPath('data.tenant.onboarding.completed', true);

        $this->assertDatabaseHas('tenants', [
            'company_name' => 'Pilot Works',
            'status' => 'active',
        ]);
        $this->assertNotNull($user->fresh()->tenant_id);
    }
}
