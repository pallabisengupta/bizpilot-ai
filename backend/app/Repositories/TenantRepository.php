<?php

namespace App\Repositories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TenantRepository
{
    public function paginateForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Tenant::query()
            ->when($user->tenant_id, fn ($query) => $query->whereKey($user->tenant_id))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Tenant
    {
        return Tenant::query()->create($data);
    }

    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->fill($data);
        $tenant->save();

        return $tenant->refresh();
    }

    public function delete(Tenant $tenant): void
    {
        $tenant->delete();
    }

    public function findBySlug(string $slug): ?Tenant
    {
        return Tenant::query()->where('slug', $slug)->first();
    }
}
