<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\TenantRepository;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_for_user_generates_unique_slug_and_attaches_user(): void
    {
        $service = new TenantService(new TenantRepository());
        $user = User::factory()->create([
            'tenant_id' => null,
        ]);

        $firstTenant = $service->createForUser($user, [
            'company_name' => 'Pilot Works',
            'timezone' => 'UTC',
            'language' => 'en',
            'status' => 'active',
        ]);

        $secondTenant = $service->createForUser($user->fresh(), [
            'company_name' => 'Pilot Works',
            'timezone' => 'UTC',
            'language' => 'en',
            'status' => 'active',
        ]);

        $this->assertSame('pilot-works', $firstTenant->slug);
        $this->assertSame('pilot-works-2', $secondTenant->slug);
        $this->assertSame($firstTenant->id, $user->fresh()->tenant_id);
    }
}
