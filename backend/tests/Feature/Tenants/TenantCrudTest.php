<?php

namespace Tests\Feature\Tenants;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_tenant(): void
    {
        $user = User::factory()->create([
            'tenant_id' => null,
        ]);
        $plan = Plan::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/tenants', [
                'company_name' => 'BizPilot Labs',
                'industry' => 'software',
                'logo' => 'https://example.com/logo.png',
                'phone' => '+15551234567',
                'email' => 'hello@bizpilot.test',
                'website' => 'https://bizpilot.test',
                'timezone' => 'UTC',
                'language' => 'en',
                'plan_id' => $plan->id,
                'status' => 'active',
            ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Tenant created successfully.')
            ->assertJsonPath('data.tenant.company_name', 'BizPilot Labs')
            ->assertJsonPath('data.tenant.slug', 'bizpilot-labs');

        $this->assertDatabaseHas('tenants', [
            'company_name' => 'BizPilot Labs',
            'slug' => 'bizpilot-labs',
        ]);
        $this->assertNotNull($user->fresh()->tenant_id);
    }

    public function test_authenticated_user_can_list_only_their_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/tenants');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $tenant->id);
    }

    public function test_authenticated_user_can_show_update_and_delete_their_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/tenants/{$tenant->id}")
            ->assertOk()
            ->assertJsonPath('data.tenant.id', $tenant->id);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/tenants/{$tenant->id}", [
                'company_name' => 'Updated Company',
                'timezone' => 'Asia/Kolkata',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Tenant updated successfully.')
            ->assertJsonPath('data.tenant.company_name', 'Updated Company')
            ->assertJsonPath('data.tenant.timezone', 'Asia/Kolkata');

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/tenants/{$tenant->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Tenant deleted successfully.');

        $this->assertSoftDeleted('tenants', [
            'id' => $tenant->id,
        ]);
    }

    public function test_user_cannot_access_another_tenant(): void
    {
        $ownTenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $ownTenant->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/tenants/{$otherTenant->id}")
            ->assertForbidden();
    }
}
