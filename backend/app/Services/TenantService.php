<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Repositories\TenantRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantService
{
    public function __construct(
        private readonly TenantRepository $tenants,
    ) {
    }

    public function listForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->tenants->paginateForUser($user, $perPage);
    }

    public function createForUser(User $user, array $data): Tenant
    {
        return DB::transaction(function () use ($user, $data): Tenant {
            $tenant = $this->tenants->create($this->normalizeData($data));

            if (! $user->tenant_id) {
                $user->forceFill([
                    'tenant_id' => $tenant->id,
                ])->save();
            }

            return $tenant;
        });
    }

    public function update(Tenant $tenant, array $data): Tenant
    {
        return $this->tenants->update($tenant, $this->normalizeData($data, false));
    }

    public function delete(Tenant $tenant): void
    {
        $this->tenants->delete($tenant);
    }

    public function completeOnboarding(User $user, array $data): Tenant
    {
        return DB::transaction(function () use ($user, $data): Tenant {
            $payload = $this->normalizeData($data, ! $user->tenant);
            $payload['status'] = $data['status'] ?? 'active';
            $payload['onboarding'] = [
                'completed' => true,
                'current_step' => 'complete',
                'completed_at' => now()->toISOString(),
            ];

            if ($user->tenant) {
                return $this->tenants->update($user->tenant, $payload);
            }

            $tenant = $this->tenants->create($payload);

            $user->forceFill([
                'tenant_id' => $tenant->id,
            ])->save();

            return $tenant;
        });
    }

    public function saveOnboardingStep(User $user, array $data): Tenant
    {
        return DB::transaction(function () use ($user, $data): Tenant {
            $tenant = $user->tenant;

            if (! $tenant) {
                $tenant = $this->tenants->create([
                    'company_name' => $data['company_name'] ?? $user->name."'s Company",
                    'slug' => $this->uniqueSlug($data['company_name'] ?? $user->name."'s Company"),
                    'timezone' => $data['timezone'] ?? 'UTC',
                    'language' => $data['language'] ?? 'en',
                    'status' => 'onboarding',
                    'onboarding' => [],
                ]);

                $user->forceFill([
                    'tenant_id' => $tenant->id,
                ])->save();
            }

            $onboarding = array_merge($tenant->onboarding ?? [], [
                'completed' => false,
                'current_step' => $data['step'],
                'steps' => array_merge($tenant->onboarding['steps'] ?? [], [
                    $data['step'] => $data['payload'] ?? [],
                ]),
            ]);

            return $this->tenants->update($tenant, [
                'onboarding' => $onboarding,
                'status' => 'onboarding',
            ]);
        });
    }

    private function normalizeData(array $data, bool $requireSlug = true): array
    {
        if (isset($data['company_name']) && (! isset($data['slug']) || $data['slug'] === '')) {
            $data['slug'] = $this->uniqueSlug($data['company_name']);
        } elseif (isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        } elseif ($requireSlug) {
            $data['slug'] = $this->uniqueSlug($data['company_name']);
        }

        return $data;
    }

    private function uniqueSlug(string $companyName): string
    {
        $baseSlug = Str::slug($companyName);
        $slug = $baseSlug;
        $counter = 2;

        while ($this->tenants->findBySlug($slug)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
