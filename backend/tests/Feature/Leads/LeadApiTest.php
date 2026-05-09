<?php

namespace Tests\Feature\Leads;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_list_tenant_leads(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/leads', [
            'name' => 'Maya Client',
            'email' => 'maya@example.com',
            'phone' => '+15551234567',
            'company_name' => 'Maya Co',
            'source' => 'whatsapp',
            'status' => 'new',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Lead created successfully.')
            ->assertJsonPath('data.lead.name', 'Maya Client');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/leads?status=new')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_assign_lead_and_add_note(): void
    {
        $tenant = Tenant::factory()->create();
        $actor = User::factory()->create(['tenant_id' => $tenant->id]);
        $assignee = User::factory()->create(['tenant_id' => $tenant->id]);
        $lead = Lead::factory()->create(['tenant_id' => $tenant->id, 'assigned_user_id' => null]);

        $this->actingAs($actor, 'sanctum')
            ->postJson("/api/v1/leads/{$lead->id}/assign", [
                'assigned_user_id' => $assignee->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.lead.assigned_user_id', $assignee->id);

        $this->actingAs($actor, 'sanctum')
            ->postJson("/api/v1/leads/{$lead->id}/notes", [
                'note' => 'Called the lead and confirmed interest.',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Lead note added successfully.');

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'activity_type' => 'note',
        ]);
    }

    public function test_user_cannot_access_another_tenant_lead(): void
    {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $lead = Lead::factory()->create(['tenant_id' => $otherTenant->id]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/leads/{$lead->id}")
            ->assertForbidden();
    }

    public function test_stats_include_total_new_and_conversion_rate(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        Lead::factory()->create(['tenant_id' => $tenant->id, 'status' => Lead::STATUS_NEW]);
        Lead::factory()->create(['tenant_id' => $tenant->id, 'status' => Lead::STATUS_WON]);
        Lead::factory()->create(['tenant_id' => $tenant->id, 'status' => Lead::STATUS_LOST]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/leads/stats')
            ->assertOk()
            ->assertJsonPath('data.total_leads', 3)
            ->assertJsonPath('data.new_leads', 1)
            ->assertJsonPath('data.conversion_rate', 33.33);
    }
}
