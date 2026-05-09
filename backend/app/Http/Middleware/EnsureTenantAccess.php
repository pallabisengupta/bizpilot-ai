<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $tenant = $this->resolveTenant($request);

        if (! $tenant) {
            abort(403, 'Tenant context is required.');
        }

        if ((int) $user->tenant_id !== (int) $tenant->id) {
            abort(403, 'You do not have access to this tenant.');
        }

        if ($tenant->status === 'suspended') {
            abort(403, 'Tenant is suspended.');
        }

        $this->tenantContext->set($tenant);

        return $next($request);
    }

    private function resolveTenant(Request $request): ?Tenant
    {
        $routeTenant = $request->route('tenant');

        if ($routeTenant instanceof Tenant) {
            return $routeTenant;
        }

        if ($routeTenant) {
            return Tenant::query()->find($routeTenant);
        }

        $tenantId = $request->header('X-Tenant-ID');

        if ($tenantId) {
            return Tenant::query()->find($tenantId);
        }

        $tenantSlug = $request->header('X-Tenant-Slug');

        if ($tenantSlug) {
            return Tenant::query()->where('slug', $tenantSlug)->first();
        }

        return $request->user()?->tenant;
    }
}
